import EventBus from "../EventBus";

export default class Controller {
    static fromAction(action) {
        return new Controller(
            action,
            new Model(),
            new View(document.querySelector(".js-" + action))
        );
    }

    constructor(action, model, view) {
        this.action = action;
        this.model = model;
        this.view = view;

        this.view.el.addEventListener("click", e => {
            this.model.toggle();
            if (this.model.isActive) {
                window.EventBus.dispatchEvent("action.selected", { action: this.action });
            } else {
                window.EventBus.dispatchEvent("action.deselected");
            }
        });

        window.EventBus.addEventListener("action.selected", e => {
            if (this.action !== e.detail.action) {
                this.model.deactivate();
            }
        });

        window.EventBus.addEventListener("action.completed", e => { this.model.deactivate(); });

        this.model.bus.addEventListener("activated", e => { this.view.activate(); });
        this.model.bus.addEventListener("deactivated", e => { this.view.deactivate(); });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    activate() {
        this.el.classList.add("selected");
    }

    deactivate() {
        this.el.classList.remove("selected");
    }
}

class Model {
    constructor() {
        this.isActive = false;

        this.bus = new EventBus();
    }

    toggle() {
        if (this.isActive) {
            this.deactivate();
        } else {
            this.activate();
        }
    }

    activate() {
        this.isActive = true;
        this.bus.dispatchEvent("activated");
    }

    deactivate() {
        this.isActive = false;
        this.bus.dispatchEvent("deactivated");
    }
}
