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
        window.EventBus.addEventListener("cancel", e => { this.model.reset(); });

        window.EventBus.addEventListener("action.triggered", e => {
            if (!this.model.isSupportedAction()) {
                return;
            }

            if (this.model.action.isUse()
                && e.detail.itemTypeId === "telephone"
            ) {
                return;
            }

            if (this.model.action.isPickUpMultiple()) {
                this.model.selectedItems.forEach(item => {
                    this.view.set("items[]", item.itemId);
                });
                this.view.submit(this.model.createPickUpUrl());
                return;
            }

            if (this.model.action.isDropMultiple()) {
                this.model.selectedItems.forEach(item => {
                    this.view.set("items[]", item.itemId);
                });
                this.view.submit(this.model.createDropUrl());
                return;
            }

            this.view.submit(
                this.model.createActionUrl(e.detail.itemId)
            );
        });

        window.EventBus.addEventListener("use.telephone", e => {
            this.view.set("number", e.detail.number);
            this.view.submit(
                this.model.createUseUrl(e.detail.itemId)
            )
        });

        window.EventBus.addEventListener("item.selected", e => {
            this.model.addSelectedItem(e.detail.itemId, e.detail.itemTypeId);
        });

        window.EventBus.addEventListener("item.unselected", e => {
            this.model.removeSelectedItem(e.detail.itemId);
        });

        this.model.bus.addEventListener("item.selected", e => {
            if (e.detail.action === "place"
                && e.detail.selectedItems.length === 2
            ) {
                this.view.set("itemSubjectId", e.detail.selectedItems[0].itemId);
                this.view.set("itemTargetId", e.detail.selectedItems[1].itemId);
                this.view.submit(this.model.createPlaceUrl());
            }
        });

        this.model.bus.addEventListener("item.empty", e => {
            window.EventBus.dispatchEvent("item.allUnselected");
        });

        this.model.bus.addEventListener("action.changed", e => {
            window.EventBus.dispatchEvent("action.changed", e.detail);
        });
    }
}

class View {
    constructor(el) {
        this.el = el;
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
        this.selectedItems = [];

        this.bus = new EventBus();
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
        this.action.toggleAltMode();
        this.dispatchActionChangedEvent();
    }

    deactivateAltMode() {
        this.action.toggleAltMode();
        this.dispatchActionChangedEvent();
    }

    activateMulMode() {
        this.action.toggleMulMode();
        this.dispatchActionChangedEvent();
    }

    deactivateMulMode() {
        this.action.toggleMulMode();
        this.dispatchActionChangedEvent();
    }

    dispatchActionChangedEvent() {
        this.bus.dispatchEvent("action.changed", { action: this.action.getName() });
    }

    isSupportedAction() {
        return ["look-at", "pick-up", "pick-up-multiple", "drop", "drop-multiple", "use", "eat"].includes(this.action.getName());
    }

    createPlaceUrl() {
        return `/${this.gameId}/place`;
    }

    createPickUpUrl() {
        return `/${this.gameId}/pick-up`;
    }

    createDropUrl() {
        return `/${this.gameId}/drop/${this.currentLocationId}`;
    }

    createActionUrl(itemId) {
        if (this.action.getName() === "look-at") {
            return "/" + this.gameId + "/look-at/" + itemId;
        }

        if (this.action.getName() === "drop") {
            return "/" + this.gameId + "/drop/" + itemId + "/" + this.currentLocationId;
        }

        if (this.action.getName() === "pick-up") {
            return "/" + this.gameId + "/pick-up/" + itemId;
        }

        if (this.action.getName() === "use") {
            return "/" + this.gameId + "/use/" + itemId;
        }

        if (this.action.getName() === "eat") {
            return "/" + this.gameId + "/eat/" + itemId;
        }

        console.error("Cannot create action URL for action:", this.action);
        return "";
    }

    createUseUrl(itemId) {
        return "/" + this.gameId + "/use/" + itemId;
    }

    addSelectedItem(itemId, itemTypeId) {
        this.selectedItems.push({
            itemId: itemId,
            itemTypeId: itemTypeId,
        });

        this.bus.dispatchEvent("item.selected", {
            action: this.action.getName(),
            altMode: this.action.altMode,
            selectedItems: this.selectedItems
        });
    }

    removeSelectedItem(itemId) {
        this.selectedItems = this.selectedItems.filter(selectedItem => {
            return selectedItem.itemId !== itemId;
        });

        if (this.selectedItems.length === 0) {
            this.bus.dispatchEvent("item.empty");
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

    toggleAltMode() {
        this.altMode = !this.altMode;
    }

    toggleMulMode() {
        this.mulMode = !this.mulMode;
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

    isPickUpMultiple() {
        return this.getName() === "pick-up-multiple";
    }

    isDropMultiple() {
        return this.getName() === "drop-multiple"
    }

    isUse() {
        return this.getName() === "use";
    }
}
