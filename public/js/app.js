/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/Components/ActionButton.js":
/*!*************************************************!*\
  !*** ./resources/js/Components/ActionButton.js ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromAction",
    value: function fromAction(action) {
      var el = document.querySelector(".js-" + action);
      return new Controller(new Model(el.dataset.defaultAction, el.dataset.defaultMultipleAction, el.dataset.altAction, el.dataset.altMultipleAction), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.el.addEventListener("click", function (e) {
      _this.model.toggle();

      if (_this.model.isButtonActive) {
        window.EventBus.dispatchEvent("actionBtn.selected", {
          button: _this.model.defaultAction
        });
      } else {
        window.EventBus.dispatchEvent("actionBtn.deselected");
      }
    });
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.handleActionChange(e.detail.action);
    });
    window.EventBus.addEventListener("alt.activated", function (e) {
      _this.model.setAltActive();
    });
    window.EventBus.addEventListener("alt.deactivated", function (e) {
      _this.model.setAltInactive();
    });
    window.EventBus.addEventListener("action.completed", function (e) {
      _this.model.deactivateButton();
    });
    window.EventBus.addEventListener("cancel", function (e) {
      _this.model.deactivateButton();
    });
    this.model.bus.addEventListener("action.activated", function (e) {
      _this.view.activateButton();
    });
    this.model.bus.addEventListener("action.deactivated", function (e) {
      _this.view.deactivateButton();
    });
    this.model.bus.addEventListener("alt.activated", function (e) {
      _this.view.activateAlt();
    });
    this.model.bus.addEventListener("alt.deactivated", function (e) {
      _this.view.deactivateAlt();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "activateButton",
    value: function activateButton() {
      this.el.classList.add("selected");
    }
  }, {
    key: "deactivateButton",
    value: function deactivateButton() {
      this.el.classList.remove("selected");
    }
  }, {
    key: "activateAlt",
    value: function activateAlt() {
      if (this.el.dataset.altAction !== "place") {
        this.el.disabled = true;
        document.querySelector(".js-make").disabled = true;
        return;
      }

      this.originalLabel = this.el.innerHTML;

      if (this.el.dataset.altLabel !== undefined) {
        this.el.innerHTML = this.el.dataset.altLabel;
      }
    }
  }, {
    key: "deactivateAlt",
    value: function deactivateAlt() {
      if (this.el.dataset.altAction !== "place") {
        this.el.disabled = false;
        document.querySelector(".js-make").disabled = false;
        return;
      }

      this.el.innerHTML = this.originalLabel;
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model(defaultAction, defaultMultipleAction, altAction, altMultipleAction) {
    _classCallCheck(this, Model);

    this.defaultAction = defaultAction;
    this.defaultMultipleAction = defaultMultipleAction;
    this.altAction = altAction;
    this.altMultipleAction = altMultipleAction;
    this.isButtonActive = false;
    this.isAlt = false;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "toggle",
    value: function toggle() {
      if (this.isButtonActive) {
        this.deactivateButton();
      } else {
        this.activateButton();
      }
    }
  }, {
    key: "handleActionChange",
    value: function handleActionChange(action) {
      if (action !== this.defaultAction && action !== this.defaultMultipleAction && action !== this.altAction && action !== this.altMultipleAction) {
        this.deactivateButton();
      }
    }
  }, {
    key: "activateButton",
    value: function activateButton() {
      this.isButtonActive = true;
      this.bus.dispatchEvent("action.activated", {
        action: this.defaultAction
      });
    }
  }, {
    key: "deactivateButton",
    value: function deactivateButton() {
      this.isButtonActive = false;
      this.bus.dispatchEvent("action.deactivated");
    }
  }, {
    key: "setAltActive",
    value: function setAltActive() {
      this.isAlt = true;
      this.bus.dispatchEvent("alt.activated");
    }
  }, {
    key: "setAltInactive",
    value: function setAltInactive() {
      this.isAlt = false;
      this.bus.dispatchEvent("alt.deactivated");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/ActionForm.js":
/*!***********************************************!*\
  !*** ./resources/js/Components/ActionForm.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromFormEl",
    value: function fromFormEl(formEl) {
      return new Controller(new Model(formEl.dataset.gameId, formEl.dataset.currentLocationId), new View(formEl));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    window.EventBus.addEventListener("actionBtn.selected", function (e) {
      _this.model.setActionButton(e.detail.button);
    });
    window.EventBus.addEventListener("actionBtn.deselected", function (e) {
      _this.model.unsetActionButton();
    });
    window.EventBus.addEventListener("alt.activated", function (e) {
      _this.model.activateAltMode();
    });
    window.EventBus.addEventListener("alt.deactivated", function (e) {
      _this.model.deactivateAltMode();
    });
    window.EventBus.addEventListener("mul.activated", function (e) {
      _this.model.activateMulMode();
    });
    window.EventBus.addEventListener("mul.deactivated", function (e) {
      _this.model.deactivateMulMode();
    });
    window.EventBus.addEventListener("cancel", function (e) {
      _this.model.reset();
    });
    window.EventBus.addEventListener("action.triggered", function (e) {
      if (!_this.model.isSupportedAction()) {
        return;
      }

      if (_this.model.action.isUse() && e.detail.itemTypeId === "telephone") {
        return;
      }

      if (_this.model.action.isPickUpMultiple()) {
        _this.model.selectedItems.forEach(function (item) {
          _this.view.set("items[]", item.itemId);
        });

        _this.view.submit(_this.model.createPickUpUrl());

        return;
      }

      if (_this.model.action.isDropMultiple()) {
        _this.model.selectedItems.forEach(function (item) {
          _this.view.set("items[]", item.itemId);
        });

        _this.view.submit(_this.model.createDropUrl());

        return;
      }

      _this.view.submit(_this.model.createActionUrl(e.detail.itemId));
    });
    window.EventBus.addEventListener("use.telephone", function (e) {
      _this.view.set("number", e.detail.number);

      _this.view.submit(_this.model.createUseUrl(e.detail.itemId));
    });
    window.EventBus.addEventListener("item.selected", function (e) {
      _this.model.addSelectedItem(e.detail.itemId, e.detail.itemTypeId);
    });
    this.model.bus.addEventListener("item.selected", function (e) {
      if (e.detail.action === "open" && e.detail.altMode === true && e.detail.selectedItems.length === 2) {
        _this.view.set("itemSubjectId", e.detail.selectedItems[0].itemId);

        _this.view.set("itemTargetId", e.detail.selectedItems[1].itemId);

        _this.view.submit(_this.model.createPlaceUrl());
      }
    });
    this.model.bus.addEventListener("action.changed", function (e) {
      window.EventBus.dispatchEvent("action.changed", e.detail);
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "set",
    value: function set(key, value) {
      var input = document.createElement("input");
      input.type = "hidden";
      input.name = key;
      input.value = value;
      this.el.append(input);
    }
  }, {
    key: "submit",
    value: function submit(actionUrl) {
      if (actionUrl === "") {
        return;
      }

      this.el.action = actionUrl;
      this.el.submit();
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model(gameId, currentLocationId) {
    _classCallCheck(this, Model);

    this.gameId = gameId;
    this.currentLocationId = currentLocationId;
    this.action = Action.createNull();
    this.selectedItems = [];
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "reset",
    value: function reset() {
      this.action = Action.createNull();
      this.selectedItems = [];
    }
  }, {
    key: "setActionButton",
    value: function setActionButton(actionButton) {
      this.action = this.action.withButton(actionButton);
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "unsetActionButton",
    value: function unsetActionButton() {
      this.action = this.action.withoutButton();
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "activateAltMode",
    value: function activateAltMode() {
      this.action.toggleAltMode();
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "deactivateAltMode",
    value: function deactivateAltMode() {
      this.action.toggleAltMode();
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "activateMulMode",
    value: function activateMulMode() {
      this.action.toggleMulMode();
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "deactivateMulMode",
    value: function deactivateMulMode() {
      this.action.toggleMulMode();
      this.dispatchActionChangedEvent();
    }
  }, {
    key: "dispatchActionChangedEvent",
    value: function dispatchActionChangedEvent() {
      this.bus.dispatchEvent("action.changed", {
        action: this.action.getName()
      });
    }
  }, {
    key: "isSupportedAction",
    value: function isSupportedAction() {
      return ["look-at", "pick-up", "pick-up-multiple", "drop", "drop-multiple", "use", "eat"].includes(this.action.getName());
    }
  }, {
    key: "createPlaceUrl",
    value: function createPlaceUrl() {
      return "/".concat(this.gameId, "/place");
    }
  }, {
    key: "createPickUpUrl",
    value: function createPickUpUrl() {
      return "/".concat(this.gameId, "/pick-up");
    }
  }, {
    key: "createDropUrl",
    value: function createDropUrl() {
      return "/".concat(this.gameId, "/drop/").concat(this.currentLocationId);
    }
  }, {
    key: "createActionUrl",
    value: function createActionUrl(itemId) {
      if (this.action.button === "look-at") {
        return "/" + this.gameId + "/look-at/" + itemId;
      }

      if (this.action.button === "drop") {
        return "/" + this.gameId + "/drop/" + itemId + "/" + this.currentLocationId;
      }

      if (this.action.button === "pick-up") {
        return "/" + this.gameId + "/pick-up/" + itemId;
      }

      if (this.action.button === "use") {
        return "/" + this.gameId + "/use/" + itemId;
      }

      if (this.action.button === "eat") {
        return "/" + this.gameId + "/eat/" + itemId;
      }

      console.error("Cannot create action URL for action:", this.action);
      return "";
    }
  }, {
    key: "createUseUrl",
    value: function createUseUrl(itemId) {
      return "/" + this.gameId + "/use/" + itemId;
    }
  }, {
    key: "addSelectedItem",
    value: function addSelectedItem(itemId, itemTypeId) {
      this.selectedItems.push({
        itemId: itemId,
        itemTypeId: itemTypeId
      });
      this.bus.dispatchEvent("item.selected", {
        action: this.action.button,
        altMode: this.action.altMode,
        selectedItems: this.selectedItems
      });
    }
  }]);

  return Model;
}();

var Action = /*#__PURE__*/function () {
  _createClass(Action, null, [{
    key: "createNull",
    value: function createNull() {
      return new Action(null, false, false);
    }
  }]);

  function Action(button, altMode, mulMode) {
    _classCallCheck(this, Action);

    this.button = button;
    this.altMode = altMode;
    this.mulMode = mulMode;
  }

  _createClass(Action, [{
    key: "withoutButton",
    value: function withoutButton() {
      return new Action(null, this.altMode, this.mulMode);
    }
  }, {
    key: "withButton",
    value: function withButton(button) {
      return new Action(button, this.altMode, this.mulMode);
    }
  }, {
    key: "toggleAltMode",
    value: function toggleAltMode() {
      this.altMode = !this.altMode;
    }
  }, {
    key: "toggleMulMode",
    value: function toggleMulMode() {
      this.mulMode = !this.mulMode;
    }
  }, {
    key: "getName",
    value: function getName() {
      if (this.isLookAt()) {
        return "look-at";
      }

      if (this.isPickUp()) {
        return "pick-up";
      }

      if (this.isPickUpMultiple()) {
        return "pick-up-multiple";
      }

      if (this.isDrop()) {
        return "drop";
      }

      if (this.isDropMultiple()) {
        return "drop-multiple";
      }

      if (this.isUse()) {
        return "use";
      }

      if (this.isEat()) {
        return "eat";
      }

      if (this.isOpen()) {
        return "open";
      }

      if (this.isPlace()) {
        return "place";
      }

      return this.button;
    }
  }, {
    key: "isLookAt",
    value: function isLookAt() {
      return this.button === "look-at" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isPickUp",
    value: function isPickUp() {
      return this.button === "pick-up" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isPickUpMultiple",
    value: function isPickUpMultiple() {
      return this.button === "pick-up" && this.altMode === false && this.mulMode === true;
    }
  }, {
    key: "isDrop",
    value: function isDrop() {
      return this.button === "drop" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isDropMultiple",
    value: function isDropMultiple() {
      return this.button === "drop" && this.altMode === false && this.mulMode === true;
    }
  }, {
    key: "isUse",
    value: function isUse() {
      return this.button === "use" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isEat",
    value: function isEat() {
      return this.button === "eat" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isOpen",
    value: function isOpen() {
      return this.button === "open" && this.altMode === false && this.mulMode === false;
    }
  }, {
    key: "isPlace",
    value: function isPlace() {
      return this.button === "open" && this.altMode === true && this.mulMode === false;
    }
  }]);

  return Action;
}();

/***/ }),

/***/ "./resources/js/Components/Alert.js":
/*!******************************************!*\
  !*** ./resources/js/Components/Alert.js ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.onClose(function (e) {
      _this.model.hide();
    });
    window.EventBus.addEventListener("action.failed", function (e) {
      _this.model.showMessage(e.detail.message);
    });
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.hide();
    });
    this.model.bus.addEventListener("shown", function (e) {
      _this.view.showMessage(e.detail.message);
    });
    this.model.bus.addEventListener("hidden", function (e) {
      _this.view.hideMessage();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
    this.$el = $(el);
  }

  _createClass(View, [{
    key: "onClose",
    value: function onClose(callback) {
      this.el.querySelector("button.close").addEventListener("click", callback);
    }
  }, {
    key: "showMessage",
    value: function showMessage(message) {
      this.el.querySelector(".js-alert-message").innerHTML = message;
      this.el.style.display = "block";
    }
  }, {
    key: "hideMessage",
    value: function hideMessage() {
      this.el.querySelector(".js-alert-message").innerHTML = "";
      this.el.style.display = "none";
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model() {
    _classCallCheck(this, Model);

    this.isShown = false;
    this.message = "";
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "showMessage",
    value: function showMessage(message) {
      this.isShown = true;
      this.message = message;
      this.bus.dispatchEvent("shown", {
        message: message
      });
    }
  }, {
    key: "hide",
    value: function hide() {
      this.isShown = false;
      this.message = "";
      this.bus.dispatchEvent("hidden");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/AltButton.js":
/*!**********************************************!*\
  !*** ./resources/js/Components/AltButton.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.onClick(function (e) {
      _this.model.toggle();
    });
    this.model.bus.addEventListener("activated", function (e) {
      window.EventBus.dispatchEvent("alt.activated");

      _this.view.setActive();
    });
    this.model.bus.addEventListener("deactivated", function (e) {
      window.EventBus.dispatchEvent("alt.deactivated");

      _this.view.setInactive();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "onClick",
    value: function onClick(callback) {
      this.el.addEventListener("click", callback);
    }
  }, {
    key: "setActive",
    value: function setActive() {
      this.el.classList.add("selected");
    }
  }, {
    key: "setInactive",
    value: function setInactive() {
      this.el.classList.remove("selected");
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model() {
    _classCallCheck(this, Model);

    this.isActive = false;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "toggle",
    value: function toggle() {
      if (this.isActive) {
        this.deactivate();
      } else {
        this.activate();
      }
    }
  }, {
    key: "activate",
    value: function activate() {
      this.isActive = true;
      this.bus.dispatchEvent("activated");
    }
  }, {
    key: "deactivate",
    value: function deactivate() {
      this.isActive = false;
      this.bus.dispatchEvent("deactivated");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/ConfirmBar.js":
/*!***********************************************!*\
  !*** ./resources/js/Components/ConfirmBar.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.onConfirm(function (e) {
      window.EventBus.dispatchEvent("action.triggered");
    });
    this.view.onCancel(function (e) {
      window.EventBus.dispatchEvent("cancel");

      _this.view.hide();
    });
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.setAction(e.detail.action);
    });
    window.EventBus.addEventListener("item.selected", function (e) {
      _this.model.show();
    });
    this.model.bus.addEventListener("show", function (e) {
      _this.view.show();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "onConfirm",
    value: function onConfirm(callback) {
      this.el.querySelector(".js-confirm").addEventListener("click", callback);
    }
  }, {
    key: "onCancel",
    value: function onCancel(callback) {
      this.el.querySelector(".js-cancel").addEventListener("click", callback);
    }
  }, {
    key: "show",
    value: function show() {
      this.el.classList.remove("confirm-bar-hidden");
    }
  }, {
    key: "hide",
    value: function hide() {
      this.el.classList.add("confirm-bar-hidden");
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model() {
    _classCallCheck(this, Model);

    this.isShown = false;
    this.action = null;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "show",
    value: function show() {
      if (this.action === "place") {
        return;
      }

      this.isShown = true;
      this.bus.dispatchEvent("show");
    }
  }, {
    key: "hide",
    value: function hide() {
      this.isShown = false;
      this.bus.dispatchEvent("hide");
    }
  }, {
    key: "setAction",
    value: function setAction(action) {
      this.action = action;
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/InventoryItem.js":
/*!**************************************************!*\
  !*** ./resources/js/Components/InventoryItem.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromItemEl",
    value: function fromItemEl(itemEl) {
      return new Controller(new Model(itemEl.dataset.id, itemEl.dataset.typeId, itemEl.dataset.label, itemEl.dataset.isContainer), new View(itemEl));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.el.addEventListener("click", function (e) {
      _this.selectItem();
    });
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.handleActionChange(e.detail.action);
    });
    window.EventBus.addEventListener("cancel", function (e) {
      _this.model.setNotSelectable();

      _this.model.setNotSelected(_this.model.id);
    });
    window.EventBus.addEventListener("action.completed", function (e) {
      _this.model.setNotSelectable();

      _this.model.setNotSelected(e.detail.itemId);
    });
    this.model.bus.addEventListener("setSelectable", function (e) {
      _this.view.setSelectable();
    });
    this.model.bus.addEventListener("setNotSelectable", function (e) {
      _this.view.unsetSelectable();
    });
    this.model.bus.addEventListener("setSelected", function (e) {
      _this.view.setSelected();
    });
    this.model.bus.addEventListener("setNotSelected", function (e) {
      _this.view.unsetSelected();
    });
  }

  _createClass(Controller, [{
    key: "selectItem",
    value: function selectItem() {
      if (this.model.isSelectable === false) {
        return;
      }

      if (this.model.action === "open" && !this.model.isContainer) {
        window.EventBus.dispatchEvent("action.failed", {
          message: "You cannot open " + this.model.label + "."
        });
        window.EventBus.dispatchEvent("action.completed", {
          action: this.model.action,
          itemId: this.model.id
        });
        return;
      }

      if (this.model.action === "place" || this.model.action === "pick-up-multiple" || this.model.action === "drop-multiple") {
        window.EventBus.dispatchEvent("item.selected", {
          itemId: this.model.id,
          itemTypeId: this.model.typeId
        });
      }

      this.model.setSelected();

      if (this.model.action !== "pick-up-multiple" && this.model.action !== "drop-multiple") {
        window.EventBus.dispatchEvent("action.triggered", {
          itemId: this.model.id,
          itemTypeId: this.model.typeId
        });
      }
    }
  }]);

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "setSelectable",
    value: function setSelectable() {
      this.el.classList.add("list-group-item-action");
      this.el.classList.add("item-selectable");
    }
  }, {
    key: "unsetSelectable",
    value: function unsetSelectable() {
      this.el.classList.remove("list-group-item-action");
      this.el.classList.remove("item-selectable");
    }
  }, {
    key: "setSelected",
    value: function setSelected() {
      this.el.classList.add("active");
    }
  }, {
    key: "unsetSelected",
    value: function unsetSelected() {
      this.el.classList.remove("active");
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model(id, typeId, label, isContainer) {
    _classCallCheck(this, Model);

    this.id = id;
    this.typeId = typeId;
    this.label = label;
    this.isContainer = isContainer;
    this.isSelectable = false;
    this.isSelected = false;
    this.action = null;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "handleActionChange",
    value: function handleActionChange(action) {
      if (action === null) {
        this.setNotSelectable();
      } else {
        this.isSelectable = true;
        this.action = action;
        this.bus.dispatchEvent("setSelectable");
      }
    }
  }, {
    key: "setNotSelectable",
    value: function setNotSelectable() {
      this.isSelectable = false;
      this.action = null;
      this.bus.dispatchEvent("setNotSelectable");
    }
  }, {
    key: "setSelected",
    value: function setSelected() {
      this.isSelected = true;
      this.bus.dispatchEvent("setSelected");
    }
  }, {
    key: "setNotSelected",
    value: function setNotSelected(itemId) {
      if (this.id !== itemId) {
        return;
      }

      this.isSelectable = false;
      this.action = null;
      this.bus.dispatchEvent("setNotSelected");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/MulButton.js":
/*!**********************************************!*\
  !*** ./resources/js/Components/MulButton.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.onClick(function (e) {
      _this.model.toggle();
    });
    this.model.bus.addEventListener("activated", function (e) {
      window.EventBus.dispatchEvent("mul.activated");

      _this.view.setActive();
    });
    this.model.bus.addEventListener("deactivated", function (e) {
      window.EventBus.dispatchEvent("mul.deactivated");

      _this.view.setInactive();
    });
    window.EventBus.addEventListener("cancel", function (e) {
      _this.model.deactivate();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
  }

  _createClass(View, [{
    key: "onClick",
    value: function onClick(callback) {
      this.el.addEventListener("click", callback);
    }
  }, {
    key: "setActive",
    value: function setActive() {
      this.el.classList.add("selected");
    }
  }, {
    key: "setInactive",
    value: function setInactive() {
      this.el.classList.remove("selected");
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model() {
    _classCallCheck(this, Model);

    this.isActive = false;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "toggle",
    value: function toggle() {
      if (this.isActive) {
        this.deactivate();
      } else {
        this.activate();
      }
    }
  }, {
    key: "activate",
    value: function activate() {
      this.isActive = true;
      this.bus.dispatchEvent("activated");
    }
  }, {
    key: "deactivate",
    value: function deactivate() {
      this.isActive = false;
      this.bus.dispatchEvent("deactivated");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/OpenModal.js":
/*!**********************************************!*\
  !*** ./resources/js/Components/OpenModal.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(el.dataset.id), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.action = e.detail.action;
    });
    window.EventBus.addEventListener("action.triggered", function (e) {
      _this.model.open(e.detail.itemId);
    });
    window.EventBus.addEventListener("alt.activated", function (e) {
      _this.model.activateAltMode();
    });
    window.EventBus.addEventListener("alt.deactivated", function (e) {
      _this.model.deactivateAltMode();
    });
    this.view.$el.on("hide.bs.modal", function (e) {
      _this.model.close();

      window.EventBus.dispatchEvent("action.completed", {
        action: _this.model.action,
        itemId: _this.model.id
      });
    });
    this.model.bus.addEventListener("opened", function (e) {
      _this.view.open();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
    this.$el = $(this.el);
  }

  _createClass(View, [{
    key: "open",
    value: function open() {
      this.$el.modal('show');
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model(id) {
    _classCallCheck(this, Model);

    this.id = id;
    this.isOpen = false;
    this.action = null;
    this.altMode = false;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "open",
    value: function open(id) {
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
  }, {
    key: "close",
    value: function close() {
      this.isOpen = false;
      this.bus.dispatchEvent("closed");
    }
  }, {
    key: "activateAltMode",
    value: function activateAltMode() {
      this.altMode = true;
    }
  }, {
    key: "deactivateAltMode",
    value: function deactivateAltMode() {
      this.altMode = false;
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/Components/PhoneModal.js":
/*!***********************************************!*\
  !*** ./resources/js/Components/PhoneModal.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../EventBus */ "./resources/js/EventBus.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var Controller = /*#__PURE__*/function () {
  _createClass(Controller, null, [{
    key: "fromEl",
    value: function fromEl(el) {
      return new Controller(new Model(), new View(el));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.onHide(function (e) {
      _this.model.close();

      window.EventBus.dispatchEvent("action.completed", {
        action: _this.model.action,
        itemId: _this.model.itemId
      });
    });
    this.view.onKeypadPress(function (e) {
      _this.model.appendNumber(e.target.dataset.symbol);
    });
    this.view.onCall(function (e) {
      window.EventBus.dispatchEvent("use.telephone", {
        itemId: _this.model.itemId,
        number: _this.model.number
      });
    });
    window.EventBus.addEventListener("action.changed", function (e) {
      _this.model.action = e.detail.action;
    });
    window.EventBus.addEventListener("action.triggered", function (e) {
      _this.model.open(e.detail.itemId, e.detail.itemTypeId);
    });
    this.model.bus.addEventListener("opened", function (e) {
      _this.view.open();
    });
    this.model.bus.addEventListener("number.updated", function (e) {
      _this.view.renderNumber(e.detail.number);
    });
    this.model.bus.addEventListener("closed", function (e) {
      _this.view.close();
    });
  }

  return Controller;
}();



var View = /*#__PURE__*/function () {
  function View(el) {
    _classCallCheck(this, View);

    this.el = el;
    this.$el = $(this.el);
  }

  _createClass(View, [{
    key: "open",
    value: function open() {
      this.$el.modal('show');
    }
  }, {
    key: "close",
    value: function close() {
      this.el.querySelector(".number-display").innerHTML = "";
    }
  }, {
    key: "renderNumber",
    value: function renderNumber(number) {
      this.el.querySelector(".number-display").innerHTML = number;
    }
  }, {
    key: "onHide",
    value: function onHide(callback) {
      this.$el.on("hide.bs.modal", callback);
    }
  }, {
    key: "onKeypadPress",
    value: function onKeypadPress(callback) {
      this.el.querySelectorAll(".keypad button").forEach(function (el) {
        el.addEventListener("click", callback);
      });
    }
  }, {
    key: "onCall",
    value: function onCall(callback) {
      this.el.querySelector(".call-button").addEventListener("click", callback);
    }
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model() {
    _classCallCheck(this, Model);

    this.itemId = null;
    this.isOpen = false;
    this.action = null;
    this.number = "";
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "open",
    value: function open(itemId, itemTypeId) {
      if (this.action !== "use") {
        return;
      }

      if (itemTypeId !== "telephone") {
        return;
      }

      this.itemId = itemId;
      this.isOpen = true;
      this.bus.dispatchEvent("opened");
    }
  }, {
    key: "appendNumber",
    value: function appendNumber(symbol) {
      if (this.number.length >= 80) {
        return;
      }

      this.number += symbol;
      this.bus.dispatchEvent("number.updated", {
        number: this.number
      });
    }
  }, {
    key: "close",
    value: function close() {
      this.isOpen = false;
      this.number = "";
      this.bus.dispatchEvent("closed");
    }
  }]);

  return Model;
}();

/***/ }),

/***/ "./resources/js/EventBus.js":
/*!**********************************!*\
  !*** ./resources/js/EventBus.js ***!
  \**********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return EventBus; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var EventBus = /*#__PURE__*/function () {
  function EventBus() {
    _classCallCheck(this, EventBus);

    this.bus = document.createElement("eventbus");
  }

  _createClass(EventBus, [{
    key: "addEventListener",
    value: function addEventListener(event, callback) {
      this.bus.addEventListener(event, callback);
    }
  }, {
    key: "removeEventListener",
    value: function removeEventListener(event, callback) {
      this.bus.removeEventListener(event, callback);
    }
  }, {
    key: "dispatchEvent",
    value: function dispatchEvent(event) {
      var detail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      this.bus.dispatchEvent(new CustomEvent(event, {
        detail: detail
      }));
    }
  }]);

  return EventBus;
}();



/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Components_ConfirmBar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Components/ConfirmBar */ "./resources/js/Components/ConfirmBar.js");
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./EventBus */ "./resources/js/EventBus.js");
/* harmony import */ var _Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Components/ActionButton */ "./resources/js/Components/ActionButton.js");
/* harmony import */ var _Components_ActionForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Components/ActionForm */ "./resources/js/Components/ActionForm.js");
/* harmony import */ var _Components_InventoryItem__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Components/InventoryItem */ "./resources/js/Components/InventoryItem.js");
/* harmony import */ var _Components_OpenModal__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Components/OpenModal */ "./resources/js/Components/OpenModal.js");
/* harmony import */ var _Components_Alert__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./Components/Alert */ "./resources/js/Components/Alert.js");
/* harmony import */ var _Components_PhoneModal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./Components/PhoneModal */ "./resources/js/Components/PhoneModal.js");
/* harmony import */ var _Components_AltButton__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./Components/AltButton */ "./resources/js/Components/AltButton.js");
/* harmony import */ var _Components_MulButton__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./Components/MulButton */ "./resources/js/Components/MulButton.js");

document.querySelectorAll(".js-increment").forEach(function (buttonEl) {
  buttonEl.addEventListener("click", function (e) {
    e.preventDefault();
    var itemEl = buttonEl.closest(".js-item");
    var selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
    var selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
    var availableQuantity = parseInt(itemEl.dataset.availableQuantity, 10);

    if (selectedQuantity === availableQuantity) {
      return;
    }

    if (selectedQuantity === 0) {
      itemEl.classList.add("active");
    }

    itemEl.dataset.selectedQuantity = selectedQuantity + 1;
    selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
    itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
  });
});
document.querySelectorAll(".js-decrement").forEach(function (buttonEl) {
  buttonEl.addEventListener("click", function (e) {
    e.preventDefault();
    var itemEl = buttonEl.closest(".js-item");
    var selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
    var selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
    var selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);

    if (selectedQuantity === 0) {
      return;
    }

    if (selectedQuantity === 1 && selectedPortions === 0) {
      itemEl.classList.remove("active");
    }

    itemEl.dataset.selectedQuantity = selectedQuantity - 1;
    selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
    itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
  });
});
document.querySelectorAll(".js-take-all").forEach(function (buttonEl) {
  buttonEl.addEventListener("click", function (e) {
    e.preventDefault();
    var itemEl = buttonEl.closest(".js-item");
    var selectedQuantityEl = itemEl.querySelector(".js-selected-quantity");
    var selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);
    var availableQuantity = parseInt(itemEl.dataset.availableQuantity, 10);

    if (selectedQuantity === availableQuantity) {
      return;
    }

    if (selectedQuantity === 0) {
      itemEl.classList.add("active");
    }

    itemEl.dataset.selectedQuantity = availableQuantity;
    selectedQuantityEl.innerHTML = itemEl.dataset.selectedQuantity;
    itemEl.querySelector(".js-quantity-input").value = itemEl.dataset.selectedQuantity;
  });
});
document.querySelectorAll(".js-portion-increment").forEach(function (buttonEl) {
  buttonEl.addEventListener("click", function (e) {
    e.preventDefault();
    var itemEl = buttonEl.closest(".js-item");
    var selectedPortionsEl = itemEl.querySelector(".js-selected-portions");
    var unselectedPortionsEl = itemEl.querySelector(".js-unselected-portions");
    var totalPortions = parseInt(itemEl.dataset.totalPortions, 10);
    var selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);
    var availablePortions = parseInt(itemEl.dataset.availablePortions, 10);

    if (selectedPortions === availablePortions) {
      return;
    }

    if (selectedPortions === 0) {
      itemEl.classList.add("active");
    }

    itemEl.dataset.selectedPortions = selectedPortions + 1;
    selectedPortionsEl.style.width = itemEl.dataset.selectedPortions / totalPortions * 100 + "%";
    unselectedPortionsEl.style.width = (availablePortions - itemEl.dataset.selectedPortions) / totalPortions * 100 + "%";
    itemEl.querySelector(".js-portions-input").value = itemEl.dataset.selectedPortions;
  });
});
document.querySelectorAll(".js-portion-decrement").forEach(function (buttonEl) {
  buttonEl.addEventListener("click", function (e) {
    e.preventDefault();
    var itemEl = buttonEl.closest(".js-item");
    var selectedPortionsEl = itemEl.querySelector(".js-selected-portions");
    var unselectedPortionsEl = itemEl.querySelector(".js-unselected-portions");
    var totalPortions = parseInt(itemEl.dataset.totalPortions, 10);
    var selectedPortions = parseInt(itemEl.dataset.selectedPortions, 10);
    var availablePortions = parseInt(itemEl.dataset.availablePortions, 10);
    var selectedQuantity = parseInt(itemEl.dataset.selectedQuantity, 10);

    if (selectedPortions === 0) {
      return;
    }

    if (selectedPortions === 1 && selectedQuantity === 0) {
      itemEl.classList.remove("active");
    }

    itemEl.dataset.selectedPortions = selectedPortions - 1;
    selectedPortionsEl.style.width = itemEl.dataset.selectedPortions / totalPortions * 100 + "%";
    unselectedPortionsEl.style.width = (availablePortions - itemEl.dataset.selectedPortions) / totalPortions * 100 + "%";
    itemEl.querySelector(".js-portions-input").value = itemEl.dataset.selectedPortions;
  });
});









window.EventBus = new _EventBus__WEBPACK_IMPORTED_MODULE_1__["default"]();
_Components_ActionForm__WEBPACK_IMPORTED_MODULE_3__["default"].fromFormEl(document.querySelector("#js-action"));
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("look-at");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("pick-up");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("drop");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("use");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("eat");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_2__["default"].fromAction("open");
_Components_AltButton__WEBPACK_IMPORTED_MODULE_8__["default"].fromEl(document.querySelector(".js-alt"));
_Components_MulButton__WEBPACK_IMPORTED_MODULE_9__["default"].fromEl(document.querySelector(".js-mul"));
_Components_ConfirmBar__WEBPACK_IMPORTED_MODULE_0__["default"].fromEl(document.querySelector(".js-confirm-bar"));
document.querySelectorAll(".js-inventory-item").forEach(function (itemEl) {
  _Components_InventoryItem__WEBPACK_IMPORTED_MODULE_4__["default"].fromItemEl(itemEl);
});
document.querySelectorAll(".js-open-modal").forEach(function (modalEl) {
  _Components_OpenModal__WEBPACK_IMPORTED_MODULE_5__["default"].fromEl(modalEl);
});
_Components_Alert__WEBPACK_IMPORTED_MODULE_6__["default"].fromEl(document.querySelector(".js-alert"));
_Components_PhoneModal__WEBPACK_IMPORTED_MODULE_7__["default"].fromEl(document.querySelector("#menu-telephone"));

/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /var/www/housequest/resources/js/app.js */"./resources/js/app.js");
module.exports = __webpack_require__(/*! /var/www/housequest/resources/sass/app.scss */"./resources/sass/app.scss");


/***/ })

/******/ });