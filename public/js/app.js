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
      return new Controller(action, new Model(), new View(document.querySelector(".js-" + action)));
    }
  }]);

  function Controller(action, model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.action = action;
    this.model = model;
    this.view = view;
    this.view.el.addEventListener("click", function (e) {
      _this.model.toggle();

      if (_this.model.isActive) {
        window.EventBus.dispatchEvent("action.selected", {
          action: _this.action
        });
      } else {
        window.EventBus.dispatchEvent("action.deselected");
      }
    });
    window.EventBus.addEventListener("action.selected", function (e) {
      if (_this.action !== e.detail.action) {
        _this.model.deactivate();
      }
    });
    this.model.bus.addEventListener("activated", function (e) {
      _this.view.activate();
    });
    this.model.bus.addEventListener("deactivated", function (e) {
      _this.view.deactivate();
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
    key: "activate",
    value: function activate() {
      this.el.classList.add("btn-primary");
      this.el.classList.remove("btn-light");
    }
  }, {
    key: "deactivate",
    value: function deactivate() {
      this.el.classList.add("btn-light");
      this.el.classList.remove("btn-primary");
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

/***/ "./resources/js/Components/ActionForm.js":
/*!***********************************************!*\
  !*** ./resources/js/Components/ActionForm.js ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Controller; });
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
    window.EventBus.addEventListener("action.selected", function (e) {
      _this.model.action = e.detail.action;
    });
    window.EventBus.addEventListener("action.triggered", function (e) {
      if (_this.model.action === null) {
        return;
      }

      _this.view.submit(_this.model.createActionUrl(e.detail.itemId));
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
    key: "submit",
    value: function submit(actionUrl) {
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
    this.action = null;
  }

  _createClass(Model, [{
    key: "createActionUrl",
    value: function createActionUrl(itemId) {
      if (this.action === "drop") {
        return "/" + this.gameId + "/drop/" + itemId + "/" + this.currentLocationId;
      }

      if (this.action === "pick-up") {
        return "/" + this.gameId + "/pick-up/" + itemId;
      }

      if (this.action === "use") {
        return "/" + this.gameId + "/use/" + itemId;
      }

      if (this.action === "eat") {
        return "/" + this.gameId + "/eat/" + itemId;
      }

      console.error("Cannot create action URL for action: " + this.action);
      return "";
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
      return new Controller(new Model(itemEl.dataset.id), new View(itemEl));
    }
  }]);

  function Controller(model, view) {
    var _this = this;

    _classCallCheck(this, Controller);

    this.model = model;
    this.view = view;
    this.view.el.addEventListener("click", function (e) {
      _this.triggerAction();
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
    window.EventBus.addEventListener("action.selected", function (e) {
      _this.model.setSelectable();
    });
    window.EventBus.addEventListener("action.deselected", function (e) {
      _this.model.setNotSelectable();
    });
  }

  _createClass(Controller, [{
    key: "triggerAction",
    value: function triggerAction() {
      if (this.model.isSelectable === false) {
        return;
      }

      this.model.setSelected();
      window.EventBus.dispatchEvent("action.triggered", {
        itemId: this.model.id
      });
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
  }]);

  return View;
}();

var Model = /*#__PURE__*/function () {
  function Model(id) {
    _classCallCheck(this, Model);

    this.id = id;
    this.isSelectable = false;
    this.isSelected = false;
    this.bus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
  }

  _createClass(Model, [{
    key: "setSelectable",
    value: function setSelectable() {
      this.isSelectable = true;
      this.bus.dispatchEvent("setSelectable");
    }
  }, {
    key: "setNotSelectable",
    value: function setNotSelectable() {
      this.isSelectable = false;
      this.bus.dispatchEvent("setNotSelectable");
    }
  }, {
    key: "setSelected",
    value: function setSelected() {
      this.isSelected = true;
      this.bus.dispatchEvent("setSelected");
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
/* harmony import */ var _EventBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./EventBus */ "./resources/js/EventBus.js");
/* harmony import */ var _Components_ActionButton__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Components/ActionButton */ "./resources/js/Components/ActionButton.js");
/* harmony import */ var _Components_ActionForm__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Components/ActionForm */ "./resources/js/Components/ActionForm.js");
/* harmony import */ var _Components_InventoryItem__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Components/InventoryItem */ "./resources/js/Components/InventoryItem.js");
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
      selectedQuantityEl.classList.add("badge-warning");
      selectedQuantityEl.classList.remove("badge-light");
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

    if (selectedQuantity === 1) {
      selectedQuantityEl.classList.add("badge-light");
      selectedQuantityEl.classList.remove("badge-warning");
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
      selectedQuantityEl.classList.add("badge-warning");
      selectedQuantityEl.classList.remove("badge-light");
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




window.EventBus = new _EventBus__WEBPACK_IMPORTED_MODULE_0__["default"]();
_Components_ActionForm__WEBPACK_IMPORTED_MODULE_2__["default"].fromFormEl(document.querySelector("#js-action"));
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_1__["default"].fromAction("pick-up");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_1__["default"].fromAction("drop");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_1__["default"].fromAction("use");
_Components_ActionButton__WEBPACK_IMPORTED_MODULE_1__["default"].fromAction("eat");
document.querySelectorAll(".js-inventory-item").forEach(function (itemEl) {
  _Components_InventoryItem__WEBPACK_IMPORTED_MODULE_3__["default"].fromItemEl(itemEl);
});

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