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

        this.view.onClose(e => { this.model.hide(); });

        window.EventBus.addEventListener("action.failed", e => { this.model.showMessage(e.detail.message); });
        window.EventBus.addEventListener("action.changed", e => { this.model.hide(); });

        this.model.bus.addEventListener("shown", e => { this.view.showMessage(e.detail.message); });
        this.model.bus.addEventListener("hidden", e => { this.view.hideMessage(); });
    }
}

class View {
    constructor(el) {
        this.el = el;
        this.$el = $(el);
    }

    onClose(callback) {
        this.el.querySelector("button.close").addEventListener("click", callback);
    }

    showMessage(message) {
        this.el.querySelector(".js-alert-message").innerHTML = message;
        this.el.style.display = "block";
    }

    hideMessage() {
        this.el.querySelector(".js-alert-message").innerHTML = "";
        this.el.style.display = "none";
    }
}

class Model {
    constructor() {
        this.isShown = false;
        this.message = "";

        this.bus = new EventBus();
    }

    showMessage(message) {
        this.isShown = true;
        this.message = message;
        this.bus.dispatchEvent("shown", { message })
    }

    hide() {
        this.isShown = false;
        this.message = "";
        this.bus.dispatchEvent("hidden");
    }
}
