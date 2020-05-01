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
            window.EventBus.dispatchEvent("confirm");
        });

        this.view.onCancel(e => {
            window.EventBus.dispatchEvent("cancel");
            this.model.hide();
        });

        window.EventBus.addEventListener("action.changed", e => { this.model.setCurrentAction(e.detail.action); });
        window.EventBus.addEventListener("item.selected", e => { this.model.show(); });
        window.EventBus.addEventListener("item.allUnselected", e => { this.model.hide(); });

        this.model.bus.addEventListener("show", e => { this.view.show(); });
        this.model.bus.addEventListener("hide", e => { this.view.hide(); });
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
        this.currentAction = null;

        this.bus = new EventBus();
    }

    show() {
        if (this.isUsedByCurrentAction()) {
            this.isShown = true;
            this.bus.dispatchEvent("show");
        }
    }

    hide() {
        this.isShown = false;
        this.bus.dispatchEvent("hide");
    }

    setCurrentAction(action) {
        this.currentAction = action;
    }

    isUsedByCurrentAction() {
        return [
            "drop-multiple",
            "pick-up-multiple",
            "use-with",
        ].includes(this.currentAction);
    }
}
