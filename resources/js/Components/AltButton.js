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

        this.view.onClick(e => {
            this.model.toggle();
            if (this.model.isActive) {
                window.EventBus.dispatchEvent("alt.activated");
            } else {
                window.EventBus.dispatchEvent("alt.deactivated");
            }
        });

        this.model.bus.addEventListener("activated", e => {
            this.view.setActive();
        });

        this.model.bus.addEventListener("deactivated", e => {
            this.view.setInactive();
        });

        window.EventBus.addEventListener("cancel", e => {
            this.model.deactivate();
        });
    }

}

class View {
    constructor(el) {
        this.el = el;
    }

    onClick(callback) {
        this.el.addEventListener("click", callback);
    }

    setActive() {
        this.el.classList.add("selected");
    }

    setInactive() {
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
