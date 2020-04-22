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

        window.EventBus.addEventListener("action.selected", e => {
            this.model.action = e.detail.action;
        });

        window.EventBus.addEventListener("action.triggered", e => {
            if (!this.model.isSupportedAction()) {
                return;
            }

            if (this.model.action === "use"
                && e.detail.itemTypeId === "telephone"
            ) {
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

        window.EventBus.addEventListener("alt.activated", e => { this.model.activateAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.deactivateAltMode(); });

        window.EventBus.addEventListener("item.selected", e => {
            this.model.addSelectedItem(e.detail.itemId, e.detail.itemTypeId);
        });

        this.model.bus.addEventListener("item.selected", e => {
            if (e.detail.action === "open"
                && e.detail.altMode === true
                && e.detail.selectedItems.length === 2
            ) {
                this.view.set("itemSubjectId", e.detail.selectedItems[0].itemId);
                this.view.set("itemTargetId", e.detail.selectedItems[1].itemId);
                this.view.submit(this.model.createPlaceUrl());
            }
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
        this.action = null;
        this.altMode = false;
        this.selectedItems = [];

        this.bus = new EventBus();
    }

    isSupportedAction() {
        return ["look-at", "drop", "pick-up", "use", "eat"].includes(this.action);
    }

    createPlaceUrl() {
        return `/${this.gameId}/place`;
    }

    createActionUrl(itemId) {
        if (this.action === "look-at") {
            return "/" + this.gameId + "/look-at/" + itemId;
        }

        if (this.action === "drop") {
            return "/" + this.gameId + "/drop/" + itemId + "/" + this.currentLocationId;
        }

        if (this.action === "pick-up") {
            return "/" + this.gameId + "/pick-up/" + itemId;
        }

        if (this.action === "use") {
            return "/" + this.gameId + "/use/" + itemId;
        }

        if (this.action === "eat") {
            return "/" + this.gameId + "/eat/" + itemId;
        }

        console.error("Cannot create action URL for action: " + this.action);
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
            action: this.action,
            altMode: this.altMode,
            selectedItems: this.selectedItems
        });
    }

    activateAltMode() {
        this.altMode = true;
    }

    deactivateAltMode() {
        this.altMode = false;
    }
}
