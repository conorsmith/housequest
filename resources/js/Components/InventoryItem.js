import EventBus from "../EventBus";

export default class Controller {
    static fromItemEl(itemEl) {
        return new Controller(
            new Model(itemEl.dataset.id, itemEl.dataset.typeId, itemEl.dataset.label, itemEl.dataset.isContainer),
            new View(itemEl)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.el.addEventListener("click", e => { this.selectItem() });

        this.model.bus.addEventListener("setSelectable", e => { this.view.setSelectable(); });
        this.model.bus.addEventListener("setNotSelectable", e => { this.view.unsetSelectable(); });
        this.model.bus.addEventListener("setSelected", e => { this.view.setSelected(); });
        this.model.bus.addEventListener("setNotSelected", e => { this.view.unsetSelected(); });

        window.EventBus.addEventListener("action.selected", e => { this.model.setSelectable(e.detail.action); });
        window.EventBus.addEventListener("action.deselected", e => { this.model.setNotSelectable(); });
        window.EventBus.addEventListener("alt.activated", e => { this.model.activateAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.deactivateAltMode(); });
        window.EventBus.addEventListener("action.completed", e => {
            this.model.setNotSelectable();
            this.model.setNotSelected(e.detail.itemId);
        });
    }

    selectItem() {
        if (this.model.isSelectable === false) {
            return;
        }

        if (this.model.action === "open"
            && this.model.altMode === false
            && !this.model.isContainer
        ) {
            window.EventBus.dispatchEvent("action.failed", {
                message: "You cannot open " + this.model.label + "."
            });
            window.EventBus.dispatchEvent("action.completed", {
                action: this.model.action,
                itemId: this.model.id
            });
            return;
        }

        if (this.model.action === "open"
            && this.model.altMode === true
        ) {
            window.EventBus.dispatchEvent("item.selected", {
                itemId: this.model.id,
                itemTypeId: this.model.typeId
            });
        }

        this.model.setSelected();

        window.EventBus.dispatchEvent("action.triggered", {
            itemId: this.model.id,
            itemTypeId: this.model.typeId
        });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    setSelectable() {
        this.el.classList.add("list-group-item-action");
        this.el.classList.add("item-selectable");
    }

    unsetSelectable() {
        this.el.classList.remove("list-group-item-action");
        this.el.classList.remove("item-selectable");
    }

    setSelected() {
        this.el.classList.add("active");
    }

    unsetSelected() {
        this.el.classList.remove("active");
    }
}

class Model {
    constructor(id, typeId, label, isContainer) {
        this.id = id;
        this.typeId = typeId;
        this.label = label;
        this.isContainer = isContainer;
        this.isSelectable = false;
        this.isSelected = false;
        this.action = null;
        this.altMode = false;

        this.bus = new EventBus();
    }

    setSelectable(action) {
        this.isSelectable = true;
        this.action = action;
        this.bus.dispatchEvent("setSelectable");
    }

    setNotSelectable() {
        this.isSelectable = false;
        this.action = null;
        this.bus.dispatchEvent("setNotSelectable");
    }

    setSelected() {
        this.isSelected = true;
        this.bus.dispatchEvent("setSelected");
    }

    setNotSelected(itemId) {
        if (this.id !== itemId) {
            return;
        }

        this.isSelectable = false;
        this.action = null;
        this.bus.dispatchEvent("setNotSelected");
    }

    activateAltMode() {
        this.altMode = true;
    }

    deactivateAltMode() {
        this.altMode = false;
    }
}
