!function(){var n={868:function(){}},o={};function t(e){var r=o[e];if(void 0!==r)return r.exports;var a=o[e]={exports:{}};return n[e](a,a.exports,t),a.exports}t.n=function(n){var o=n&&n.__esModule?function(){return n.default}:function(){return n};return t.d(o,{a:o}),o},t.d=function(n,o){for(var e in o)t.o(o,e)&&!t.o(n,e)&&Object.defineProperty(n,e,{enumerable:!0,get:o[e]})},t.o=function(n,o){return Object.prototype.hasOwnProperty.call(n,o)},function(){"use strict";t(868);const{wp:n}=window;n.domReady((function(){const{current_option:n,options:o}=window.bogoLanguageDropdown,t=`<div class="bogo-dropdown__button" tabindex="0">\n    <i class="flag flag-${n.locale}"></i>\n    <span>${n.label}</span>\n  </div>`;let e="";o.forEach((n=>{const o="draft"===n.status?"<b>DRAFT</b>":"";e+=`<li>\n      <a href="${n.url}">\n        <i class="flag flag-${n.locale}"></i>\n        <span>\n          ${n.label}\n          ${o}\n        </span>\n      </a>\n    </li>`}));const r=`<div class="bogo-dropdown">\n    ${t}\n    <ul class="bogo-dropdown__links">\n      ${e}\n    </ul>\n  </div>`;setTimeout((function(){const n=document.querySelector(".edit-post-header__toolbar");n&&n.insertAdjacentHTML("beforeend",r)}),500)}))}()}();