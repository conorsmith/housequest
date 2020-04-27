import EventBus from "../EventBus";
import Item from "../Values/Item";

export default class Controller {
    static fromItemEl(itemEl) {
        return new Controller(
            new Model(
                new Item(
                    itemEl.dataset.id,
                    itemEl.dataset.typeId,
                    itemEl.dataset.label,
                    itemEl.dataset.isContainer,
                    itemEl.dataset.whereaboutsId,
                    itemEl.dataset.whereaboutsType
                )
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
        window.EventBus.addEventListener("item.open", e => {
            this.model.showIfInContainer(e.detail.container);
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

        this.model.bus.addEventListener("show", e => {
            this.view.show();
            window.EventBus.dispatchEvent("action.completed", {
                action: this.model.action,
                itemId: e.detail.containerId
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

    show() {
        this.el.classList.remove("item-hidden");
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

    showIfInContainer(container) {
        if (this.item.whereaboutsType !== "item-contents") {
            return;
        }

        if (this.item.whereaboutsId !== container.id) {
            return;
        }

        this.bus.dispatchEvent("show", {
            containerId: container.id
        });
    }
}
