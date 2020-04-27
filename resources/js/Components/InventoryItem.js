import EventBus from "../EventBus";
import Item from "../Values/Item";

export default class Controller {
    static fromItemEl(itemEl) {
        return new Controller(
            new Model(
                new Item(itemEl.dataset.id, itemEl.dataset.typeId, itemEl.dataset.label, itemEl.dataset.isContainer)
            ),
            new View(itemEl)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.el.addEventListener("click", e => { this.selectItem() });

        window.EventBus.addEventListener("action.changed", e => { this.model.handleActionChange(e.detail.action) });
        window.EventBus.addEventListener("cancel", e => {
            this.model.setNotSelectable();
            this.model.setNotSelected();
        });
        window.EventBus.addEventListener("action.completed", e => {
            this.model.setNotSelectable();
            if (this.model.item.id === e.detail.itemId) {
                this.model.setNotSelected();
            }
        });

        this.model.bus.addEventListener("setSelectable", e => { this.view.setSelectable(); });
        this.model.bus.addEventListener("setNotSelectable", e => { this.view.unsetSelectable(); });
        this.model.bus.addEventListener("setSelected", e => { this.view.setSelected(); });
        this.model.bus.addEventListener("setNotSelected", e => {
            this.view.unsetSelected();
            window.EventBus.dispatchEvent("item.unselected", {
                itemId: this.model.item.id
            });
        });
    }

    selectItem() {
        if (this.model.isSelectable === false) {
            return;
        }

        if (this.model.isSelected === true) {
            this.model.setNotSelected();
            return;
        }

        this.model.setSelected();

        window.EventBus.dispatchEvent("item.selected", {
            item: this.model.item
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
    constructor(item) {
        this.item = item;
        this.isSelectable = false;
        this.isSelected = false;
        this.action = null;

        this.bus = new EventBus();
    }

    handleActionChange(action) {
        if (action === null) {
            this.setNotSelectable();
        } else {
            this.isSelectable = true;
            this.action = action;
            this.bus.dispatchEvent("setSelectable");
        }
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

    setNotSelected() {
        this.isSelected = false;
        this.bus.dispatchEvent("setNotSelected");
    }
}
