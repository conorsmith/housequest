import EventBus from "../EventBus";

export default class Controller {
    static fromAction(action) {
        const el = document.querySelector(".js-" + action);
        return new Controller(
            action,
            new Model(el.dataset.altLabel),
            new View(el)
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
                this.model.deactivateAction();
            }
        });

        window.EventBus.addEventListener("alt.activated", e => { this.model.activateAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.deactivateAltMode(); });

        window.EventBus.addEventListener("action.completed", e => { this.model.deactivateAction(); });

        this.model.bus.addEventListener("action.activated", e => { this.view.activateAction(); });
        this.model.bus.addEventListener("action.deactivated", e => { this.view.deactivateAction(); });
        this.model.bus.addEventListener("alt.activated", e => { this.view.activateAlt(); });
        this.model.bus.addEventListener("alt.deactivated", e => { this.view.deactivateAlt(); });
    }
}

class View {
    constructor(el) {
        this.el = el;
    }

    activateAction() {
        this.el.classList.add("selected");
    }

    deactivateAction() {
        this.el.classList.remove("selected");
    }

    activateAlt() {
        if (this.el.dataset.altLabel !== "Place") {
            this.el.disabled = true;
            document.querySelector(".js-make").disabled = true;
            return;
        }

        this.originalLabel = this.el.innerHTML;
        if (this.el.dataset.altLabel !== undefined) {
            this.el.innerHTML = this.el.dataset.altLabel;
        }
    }

    deactivateAlt() {
        if (this.el.dataset.altLabel !== "Place") {
            this.el.disabled = false;
            document.querySelector(".js-make").disabled = false;
            return;
        }

        this.el.innerHTML = this.originalLabel;
    }
}

class Model {
    constructor(altLabel) {
        this.altLabel = altLabel;
        this.isActionActive = false;
        this.isAltActive = false;

        this.bus = new EventBus();
    }

    toggle() {
        if (this.isActive) {
            this.deactivateAction();
        } else {
            this.activateAction();
        }
    }

    activateAction() {
        this.isActive = true;
        this.bus.dispatchEvent("action.activated");
    }

    deactivateAction() {
        this.isActive = false;
        this.bus.dispatchEvent("action.deactivated");
    }

    activateAltMode() {
        this.isAltActive = true;
        this.bus.dispatchEvent("alt.activated");
    }

    deactivateAltMode() {
        this.isAltActive = false;
        this.bus.dispatchEvent("alt.deactivated");
    }
}
