import EventBus from "../EventBus";

export default class Controller {
    static fromFormEl(formEl) {
        return new Controller(
            new Model(
                formEl.dataset.gameId,
                formEl.dataset.currentLocationId
            ),
            new View(formEl)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        window.EventBus.addEventListener("actionBtn.selected", e => { this.model.setActionButton(e.detail.actions);});
        window.EventBus.addEventListener("actionBtn.deselected", e => { this.model.unsetActionButton() });
        window.EventBus.addEventListener("alt.activated", e => { this.model.activateAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.deactivateAltMode(); });
        window.EventBus.addEventListener("mul.activated", e => { this.model.activateMulMode(); });
        window.EventBus.addEventListener("mul.deactivated", e => { this.model.deactivateMulMode(); });

        window.EventBus.addEventListener("item.selected", e => { this.model.addSelectedItem(e.detail.item); });
        window.EventBus.addEventListener("item.unselected", e => { this.model.removeSelectedItem(e.detail.itemId); });

        window.EventBus.addEventListener("player.selected", e => { this.model.selectPlayer(); });
        window.EventBus.addEventListener("player.unselected", e => { this.model.unselectPlayer(); });

        window.EventBus.addEventListener("confirm", e => { this.model.confirm(); });
        window.EventBus.addEventListener("cancel", e => { this.model.reset(); });

        window.EventBus.addEventListener("use.telephone", e => {
            this.model.confirm({
                number: e.detail.number
            });
        });

        this.model.bus.addEventListener("action.changed", e => {
            window.EventBus.dispatchEvent("action.changed", e.detail);
        });

        this.model.bus.addEventListener("item.empty", e => {
            window.EventBus.dispatchEvent("item.allUnselected");
        });

        this.model.bus.addEventListener("request", e => {
            if (e.detail.body !== undefined) {
                e.detail.body.forEach(([key, value]) => {
                    this.view.set(key, value);
                });
            }
            this.view.submit(e.detail.url);
        });

        this.model.bus.addEventListener("failure", e => {
            window.EventBus.dispatchEvent("action.failed", {
                message: e.detail.message
            });
            window.EventBus.dispatchEvent("action.completed", {
                action: e.detail.action,
                itemId: e.detail.itemId
            });
        });

        this.model.bus.addEventListener("open", e => {
            window.EventBus.dispatchEvent("item.open", {
                container: e.detail.container
            });
            window.EventBus.dispatchEvent("action.completed", {
                action: e.detail.action,
                itemId: e.detail.container.id
            });
            this.sendAsyncRequest(`${this.model.gameId}/open`, {
                itemId: e.detail.container.id
            });
        });

        this.model.bus.addEventListener("close", e => {
            window.EventBus.dispatchEvent("item.close", {
                container: e.detail.container
            });
            window.EventBus.dispatchEvent("action.completed", {
                action: e.detail.action,
                itemId: e.detail.container.id
            });
            this.sendAsyncRequest(`${this.model.gameId}/close`, {
                itemId: e.detail.container.id
            });
        });
    }

    sendAsyncRequest(url, body) {
        fetch(url, {
            method: "POST",
            body: new URLSearchParams(body),
            headers: new Headers({
                'X-CSRF-TOKEN': this.view.getCsrfToken()
            })
        }).then(response => {
        }).catch(error => {
            console.error(error);
        });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    getCsrfToken() {
        return this.el.querySelector("input[name=\"_token\"]").value;
    }

    set(key, value) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = value;
        this.el.append(input);
    }

    submit(actionUrl) {
        if (actionUrl === "") {
            return;
        }

        this.el.action = actionUrl;
        this.el.submit();
    }
}

class Model {
    constructor(gameId, currentLocationId) {
        this.gameId = gameId;
        this.currentLocationId = currentLocationId;
        this.action = Action.createNull();
        this.playerIsSelected = false;
        this.selectedItems = [];
        this.confirmed = false;
        this.confirmationData = undefined;

        this.bus = new EventBus();
    }

    confirm(confirmationData) {
        this.confirmed = true;
        this.confirmationData = confirmationData;
        this.dispatchActionTriggeredEvent();
    }

    reset() {
        this.action = Action.createNull();
        this.selectedItems = [];
    }

    setActionButton(actions) {
        this.action = this.action.withActions(actions);
        this.dispatchActionChangedEvent();
    }

    unsetActionButton() {
        this.action = this.action.withoutActions();
        this.dispatchActionChangedEvent();
    }

    activateAltMode() {
        this.action.activateAltMode();
        this.dispatchActionChangedEvent();
    }

    deactivateAltMode() {
        this.action.deactivateAltMode();
        this.dispatchActionChangedEvent();
    }

    activateMulMode() {
        this.action.activateMulMode();
        this.dispatchActionChangedEvent();
    }

    deactivateMulMode() {
        this.action.deactivateMulMode();
        this.dispatchActionChangedEvent();
    }

    addSelectedItem(item) {
        this.selectedItems.push(item);
        this.dispatchActionTriggeredEvent(item);
    }

    removeSelectedItem(itemId) {
        this.selectedItems = this.selectedItems.filter(selectedItem => {
            return selectedItem.id !== itemId;
        });

        if (this.selectedItems.length === 0) {
            this.bus.dispatchEvent("item.empty");
        }
    }

    selectPlayer() {
        this.playerIsSelected = true;
        this.selectedItems.push({
            id: "00000000-0000-0000-0000-000000000000",
            typeId: "player",
            label: "yourself",
            isContainer: false
        });
        this.dispatchActionTriggeredEvent();
    }

    unselectPlayer() {
        this.playerIsSelected = false;
        this.selectedItems = this.selectedItems.filter(selectedItem => {
            return selectedItem.id !== "00000000-0000-0000-0000-000000000000";
        });

        if (this.selectedItems.length === 0) {
            this.bus.dispatchEvent("item.empty");
        }
    }

    dispatchActionChangedEvent() {
        this.bus.dispatchEvent("action.changed", { action: this.action.getName() });
    }

    dispatchActionTriggeredEvent(item) {
        if (item === undefined) {
            item = this.selectedItems[this.selectedItems.length - 1];
        }

        if (this.action.is("look-at")) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/look-at/${item.id}`
            });
        }

        if (this.action.is("pick-up")) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/pick-up`,
                body: [
                    ["items[]", item.id],
                ]
            });
        }

        if (this.action.is("pick-up-multiple")
            && this.confirmed === true
        ) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/pick-up`,
                body: this.selectedItems.map(item => {
                        return ["items[]", item.id];
                    })
            });
        }

        if (this.action.is("drop")) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/drop/${this.currentLocationId}`,
                body: [
                    ["items[]", item.id],
                ]
            });
        }

        if (this.action.is("drop-multiple")
            && this.confirmed === true
        ) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/drop/${this.currentLocationId}`,
                body: this.selectedItems.map(item => {
                    return ["items[]", item.id];
                })
            });
        }

        if (this.action.is("use")) {
            let body = [];

            if (item.typeId === "telephone") {
                if (this.confirmed === false) {
                    return;
                }
                body = Object.entries(this.confirmationData).map(([key, value]) => {
                    return [key, value];
                });
            }

            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/use/${item.id}`,
                body: body
            });
        }

        if (this.action.is("use-with")
            && this.confirmed === true
        ) {
            this.bus.dispatchEvent("request", {
                url: `${this.gameId}/use-with`,
                body: this.selectedItems.map(item => {
                    return ["items[]", item.id];
                }),
            })
        }

        if (this.action.is("open")) {
            if (!item.isContainer) {
                this.bus.dispatchEvent("failure", {
                    message: `You cannot open ${item.label}.`,
                    action: this.action.getName(),
                    itemId: item.id
                });
            } else {
                this.bus.dispatchEvent("open", {
                    action: this.action.getName(),
                    container: item
                });
            }
        }

        if (this.action.is("close")) {
            if (!item.isContainer) {
                this.bus.dispatchEvent("failure", {
                    message: `You cannot close ${item.label}.`,
                    action: this.action.getName(),
                    itemId: item.id
                });
            } else {
                this.bus.dispatchEvent("close", {
                    action: this.action.getName(),
                    container: item
                });
            }
        }

        if (this.action.is("put-in")
            && this.selectedItems.length === 2
        ) {
            if (!this.selectedItems[1].isContainer) {
                this.bus.dispatchEvent("failure", {
                    message: `You cannot put ${this.selectedItems[0].label} in ${this.selectedItems[1].label}.`,
                    action: this.action.getName(),
                    itemId: item.id
                });
            } else {
                this.bus.dispatchEvent("request", {
                    url: `/${this.gameId}/put-in`,
                    body: [
                        ["itemSubjectId", this.selectedItems[0].id],
                        ["itemTargetId", this.selectedItems[1].id],
                    ]
                });
            }
        }

        if (this.action.is("place")
            && this.selectedItems.length === 2
        ) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/place`,
                body: [
                    ["itemSubjectId", this.selectedItems[0].id],
                    ["itemTargetId", this.selectedItems[1].id],
                ]
            });
        }

        if (this.action.is("eat")) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/eat/${item.id}`
            });
        }

        if (this.action.is("eat-multiple")
            && this.confirmed === true
        ) {
            this.bus.dispatchEvent("request", {
                url: `/${this.gameId}/eat`,
                body: this.selectedItems.map(item => {
                    return ["items[]", item.id];
                })
            });
        }
    }
}

class Action {
    static createNull() {
        return new Action(null, false, false);
    }

    constructor(actions, altMode, mulMode) {
        this.actions = actions;
        this.altMode = altMode;
        this.mulMode = mulMode;
    }

    withActions(actions) {
        return new Action(actions, this.altMode, this.mulMode);
    }

    withoutActions() {
        return new Action(null, this.altMode, this.mulMode);
    }

    activateAltMode() {
        this.altMode = true;
    }

    deactivateAltMode() {
        this.altMode = false;
    }

    activateMulMode() {
        this.mulMode = true;
    }

    deactivateMulMode() {
        this.mulMode = false;
    }

    getName() {
        if (this.actions === null) {
            return undefined;
        }

        if (this.altMode === false
            && this.mulMode === false
        ) {
            return this.actions.defaultAction;
        }

        if (this.altMode === false
            && this.mulMode === true
        ) {
            return this.actions.defaultMultipleAction;
        }

        if (this.altMode === true
            && this.mulMode === false
        ) {
            return this.actions.altAction;
        }

        if (this.altMode === true
            && this.mulMode === true
        ) {
            return this.actions.altMultipleAction;
        }

        return undefined;
    }

    is(name) {
        return name === this.getName();
    }
}
