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

        this.view.onConfirm(e => {
            window.EventBus.dispatchEvent("action.triggered");
        });

        this.view.onCancel(e => {
            window.EventBus.dispatchEvent("cancel");
            this.view.hide();
        });

        window.EventBus.addEventListener("action.changed", e => { this.model.setAction(e.detail.action); });
        window.EventBus.addEventListener("item.selected", e => { this.model.show(); });

        this.model.bus.addEventListener("show", e => { this.view.show(); });
    }

}

class View {
    constructor(el) {
        this.el = el;
    }

    onConfirm(callback) {
        this.el.querySelector(".js-confirm").addEventListener("click", callback);
    }

    onCancel(callback) {
        this.el.querySelector(".js-cancel").addEventListener("click", callback);
    }

    show() {
        this.el.classList.remove("confirm-bar-hidden");
    }

    hide() {
        this.el.classList.add("confirm-bar-hidden");
    }
}

class Model {
    constructor() {
        this.isShown = false;
        this.action = null;

        this.bus = new EventBus();
    }

    show() {
        if (this.action === "place") {
            return;
        }
        this.isShown = true;
        this.bus.dispatchEvent("show");
    }

    hide() {
        this.isShown = false;
        this.bus.dispatchEvent("hide");
    }

    setAction(action) {
        this.action = action;
    }
}
