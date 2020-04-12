import EventBus from "../EventBus";

export default class Controller {
    static fromItemEl(itemEl) {
        return new Controller(
            new Model(itemEl.dataset.id),
            new View(itemEl)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.el.addEventListener("click", e => { this.triggerAction() });

        this.model.bus.addEventListener("setSelectable", e => { this.view.setSelectable(); });
        this.model.bus.addEventListener("setNotSelectable", e => { this.view.unsetSelectable(); });
        this.model.bus.addEventListener("setSelected", e => { this.view.setSelected(); });

        window.EventBus.addEventListener("action.selected", e => { this.model.setSelectable(); });
        window.EventBus.addEventListener("action.deselected", e => { this.model.setNotSelectable(); });
    }

    triggerAction() {
        if (this.model.isSelectable === false) {
            return;
        }

        this.model.setSelected();

        window.EventBus.dispatchEvent("action.triggered", {
            itemId: this.model.id
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
}

class Model {
    constructor(id) {
        this.id = id;
        this.isSelectable = false;
        this.isSelected = false;

        this.bus = new EventBus();
    }

    setSelectable() {
        this.isSelectable = true;
        this.bus.dispatchEvent("setSelectable");
    }

    setNotSelectable() {
        this.isSelectable = false;
        this.bus.dispatchEvent("setNotSelectable");
    }

    setSelected() {
        this.isSelected = true;
        this.bus.dispatchEvent("setSelected");
    }
}
