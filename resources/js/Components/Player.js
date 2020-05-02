import EventBus from "../EventBus";

export default class Controller {
    static fromEl(el) {
        return new Controller(
            new Model(),
            new View(el)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        this.view.onClick(e => { this.selectPlayer() });

        window.EventBus.addEventListener("action.changed", e => { this.model.handleActionChange(e.detail.action) });
        window.EventBus.addEventListener("cancel", e => {
            this.model.setNotSelectable();
            this.model.setNotSelected();
        });
        window.EventBus.addEventListener("action.completed", e => {
            this.model.setNotSelectable();
            this.model.setNotSelected();
        });

        this.model.bus.addEventListener("setSelectable", e => { this.view.setSelectable(); });
        this.model.bus.addEventListener("setNotSelectable", e => { this.view.unsetSelectable(); });
        this.model.bus.addEventListener("setSelected", e => { this.view.setSelected(); });
        this.model.bus.addEventListener("setNotSelected", e => {
            this.view.unsetSelected();
            window.EventBus.dispatchEvent("player.unselected");
        });
    }

    selectPlayer() {
        if (this.model.isSelectable === false) {
            return;
        }

        if (this.model.isSelected === true) {
            this.model.setNotSelected();
            return;
        }

        this.model.setSelected();

        window.EventBus.dispatchEvent("player.selected");
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    onClick(callback) {
        this.el.addEventListener("click", callback);
    }

    setSelectable() {
        this.el.classList.add("header-selectable");
    }

    unsetSelectable() {
        this.el.classList.remove("header-selectable");
    }

    setSelected() {
        this.el.classList.add("active");
    }

    unsetSelected() {
        this.el.classList.remove("active");
    }
}

class Model {
    constructor() {
        this.isSelectable = false;
        this.isSelected = false;
        this.action = null;

        this.bus = new EventBus();
    }

    handleActionChange(action) {
        if (action === undefined) {
            this.setNotSelectable();
            this.setNotSelected();
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
        if (this.isSelected) {
            this.isSelected = false;
            this.bus.dispatchEvent("setNotSelected");
        }
    }
}
