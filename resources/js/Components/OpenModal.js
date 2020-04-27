import EventBus from "../EventBus";

export default class Controller {
    static fromEl(el) {
        return new Controller(
            new Model(el.dataset.id),
            new View(el)
        );
    }

    constructor(model, view) {
        this.model = model;
        this.view = view;

        window.EventBus.addEventListener("action.changed", e => { this.model.action = e.detail.action; });

        window.EventBus.addEventListener("item.selected", e => {
            this.model.open(e.detail.item.id);
        });

        window.EventBus.addEventListener("alt.activated", e => { this.model.activateAltMode(); });
        window.EventBus.addEventListener("alt.deactivated", e => { this.model.deactivateAltMode(); });

        this.view.$el.on("hide.bs.modal", e => {
            this.model.close();
            window.EventBus.dispatchEvent("action.completed", {
                action: this.model.action,
                itemId: this.model.id
            });
        });

        this.model.bus.addEventListener("opened", e => { this.view.open() });
    }
}

class View {
    constructor(el) {
        this.el = el;
        this.$el = $(this.el);
    }

    open() {
        this.$el.modal('show');
    }
}

class Model {
    constructor(id) {
        this.id = id;
        this.isOpen = false;
        this.action = null;
        this.altMode = false;

        this.bus = new EventBus();
    }

    open(id) {
        if (this.action !== "open") {
            return;
        }

        if (this.altMode === true) {
            return;
        }

        if (this.id !== id) {
            return;
        }

        this.isOpen = true;
        this.bus.dispatchEvent("opened");
    }

    close() {
        this.isOpen = false;
        this.bus.dispatchEvent("closed");
    }

    activateAltMode() {
        this.altMode = true;
    }

    deactivateAltMode() {
        this.altMode = false;
    }
}
