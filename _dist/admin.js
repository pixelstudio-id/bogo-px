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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "../../../../../../_open/bogo-px/custom/assets/admin.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "../../../../../../_open/bogo-px/custom/assets/_MyFetch.js":
/*!**************************************************!*\
  !*** H:/_open/bogo-px/custom/assets/_MyFetch.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/**\r\n * Simple GET and POST functions that return Promise.\r\n *\r\n * Reference:\r\n *   axios-lite https://github.com/piyas1234/react-server-request\r\n *\r\n * Example:\r\n *   const myFetch = new MyFetch('https://mysite.com/wp-json/my/v1');\r\n *   myFetch.get('/posts');\r\n *   myFetch.post('/create-post', {...});\r\n */\r\nclass MyFetch {\r\n  constructor(baseURL, headers = {}) {\r\n    this.baseURL = baseURL;\r\n    this.headers = {\r\n      'Content-Type': 'application/json',\r\n      ...headers,\r\n    };\r\n  }\r\n\r\n  /**\r\n   * @param string path - path to append to the base URL\r\n   */\r\n  async get(path) {\r\n    try {\r\n      const response = await fetch(this.baseURL + path, {\r\n        method: 'GET',\r\n        headers: this.headers,\r\n      });\r\n\r\n      if (!response.ok) {\r\n        return Promise.reject(await response.json());\r\n      }\r\n\r\n      return response.json();\r\n    } catch (err) {\r\n      console.log(err);\r\n    }\r\n  }\r\n\r\n  /**\r\n   * Get data from sessionStorage, if not found then do API request and save it to sessionStorage\r\n   */\r\n  async getFromSession(path) {\r\n    const cached = sessionStorage.getItem(path);\r\n    if (cached) {\r\n      return JSON.parse(cached);\r\n    }\r\n\r\n    const data = await this.get(path);\r\n    sessionStorage.setItem(path, JSON.stringify(data));\r\n    return data;\r\n  }\r\n\r\n  /**\r\n   * Get data from localStorage, if not found then do API request and save it to localStorage\r\n   */\r\n  async getFromLocal(path) {\r\n    const cached = localStorage.getItem(path);\r\n    if (cached) {\r\n      return JSON.parse(cached);\r\n    }\r\n\r\n    const data = await this.get(path);\r\n    localStorage.setItem(path, JSON.stringify(data));\r\n    return data;\r\n  }\r\n\r\n  async post(apiPath, body) {\r\n    let bodyData = null;\r\n    if (body instanceof Element) {\r\n      bodyData = new FormData(body);\r\n    } else {\r\n      bodyData = JSON.stringify(body);\r\n    }\r\n\r\n    try {\r\n      const response = await fetch(this.baseURL + apiPath, {\r\n        method: 'POST',\r\n        body: bodyData,\r\n        headers: this.headers,\r\n      });\r\n\r\n      if (!response.ok) {\r\n        return Promise.reject(await response.json());\r\n      }\r\n\r\n      return response.json();\r\n    } catch (err) {\r\n      console.log(err);\r\n    }\r\n  }\r\n\r\n  async delete(apiPath) {\r\n    try {\r\n      const response = await fetch(this.url + apiPath, {\r\n        method: 'DELETE',\r\n      });\r\n\r\n      return await response;\r\n    } catch (err) {\r\n      console.log(err);\r\n    }\r\n  }\r\n}\r\n\r\n/* harmony default export */ __webpack_exports__[\"default\"] = (MyFetch);\r\n\n\n//# sourceURL=webpack:///H:/_open/bogo-px/custom/assets/_MyFetch.js?");

/***/ }),

/***/ "../../../../../../_open/bogo-px/custom/assets/admin.js":
/*!***********************************************!*\
  !*** H:/_open/bogo-px/custom/assets/admin.js ***!
  \***********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _MyFetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_MyFetch */ \"../../../../../../_open/bogo-px/custom/assets/_MyFetch.js\");\n/* harmony import */ var _admin_sass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./admin.sass */ \"../../../../../../_open/bogo-px/custom/assets/admin.sass\");\n/* harmony import */ var _admin_sass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_admin_sass__WEBPACK_IMPORTED_MODULE_1__);\n\r\n\r\n\r\nconst localeColumn = {\r\n  init() {\r\n    const $buttons = document.querySelectorAll('.column-locale__inner a');\r\n    if (!$buttons) { return; }\r\n\r\n    $buttons.forEach(($b) => {\r\n      $b.addEventListener('click', this.onClick);\r\n    });\r\n  },\r\n\r\n  async onClick(e) {\r\n    const $button = e.currentTarget;\r\n    if ($button.getAttribute('href')) { return; }\r\n\r\n    e.preventDefault();\r\n    const { id, locale } = $button.dataset;\r\n    const myFetch = new _MyFetch__WEBPACK_IMPORTED_MODULE_0__[\"default\"](window.bogo.apiSettings.root, {\r\n      'X-WP-Nonce': window.bogo.apiSettings.nonce,\r\n    });\r\n\r\n    try {\r\n      $button.classList.add('is-loading');\r\n      $button.setAttribute('href', '#'); // add href so it's not clickable again\r\n\r\n      const result = await myFetch.post(`/posts/${id}/translations/${locale}`, {});\r\n      $button.classList.remove('is-loading');\r\n\r\n      const localePost = result[locale];\r\n      if (localePost) {\r\n        $button.classList.add('is-status-draft');\r\n        $button.setAttribute('href', localePost.edit_link);\r\n        $button.setAttribute('target', '_blank');\r\n\r\n        window.open(localePost.edit_link, '_blank');\r\n      }\r\n    } catch (err) {\r\n      console.log(err);\r\n    }\r\n  },\r\n};\r\n\r\nfunction onReady() {\r\n  localeColumn.init();\r\n}\r\n\r\ndocument.addEventListener('DOMContentLoaded', onReady);\r\n\n\n//# sourceURL=webpack:///H:/_open/bogo-px/custom/assets/admin.js?");

/***/ }),

/***/ "../../../../../../_open/bogo-px/custom/assets/admin.sass":
/*!*************************************************!*\
  !*** H:/_open/bogo-px/custom/assets/admin.sass ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///H:/_open/bogo-px/custom/assets/admin.sass?");

/***/ })

/******/ });