!function(){var e={948:function(){},400:function(){},522:function(){},806:function(){}},t={};function n(o){var r=t[o];if(void 0!==r)return r.exports;var s=t[o]={exports:{}};return e[o](s,s.exports,n),s.exports}!function(){"use strict";class e{constructor(e,t){this.url=e||"",this.headers={"Content-Type":"application/json",...t}}async get(e){try{const t=await fetch(this.url+e,{method:"GET",headers:this.headers});return t.ok?t.json():Promise.reject(await t.json())}catch(e){return console.log(e),Promise.reject(e)}}async getFromSession(e){const t=sessionStorage.getItem(e);if(t&&"undefined"!==t)return JSON.parse(t);try{const t=await this.get(e);return sessionStorage.setItem(e,JSON.stringify(t)),t}catch(e){return Promise.reject(e)}}async getFromLocal(e){const t=localStorage.getItem(e);if(t&&"undefined"!==t)return JSON.parse(t);try{const t=await this.get(e);return localStorage.setItem(e,JSON.stringify(t)),t}catch(e){return Promise.reject(e)}}async post(e,t){let n=null,{headers:o}=this;t instanceof Element?(n=new FormData(t),o={}):n=JSON.stringify(t);try{const t=await fetch(this.url+e,{method:"POST",body:n,headers:o});return t.ok?t.json():Promise.reject(await t.json())}catch(e){return console.log(e),Promise.reject(e)}}async delete(e){try{return await fetch(this.url+e,{method:"DELETE"})}catch(e){return console.log(e),Promise.reject(e)}}}const t={},{nonce:o,root:r}=window.bogoApiSettings;o&&(t["X-WP-Nonce"]=o),new e;const s=new e(r,t);n(400),document.addEventListener("DOMContentLoaded",(()=>{(()=>{const e=document.querySelectorAll(".column-locale__inner a");async function t(e){const t=e.currentTarget;if(t.getAttribute("href"))return;e.preventDefault();const{id:n,locale:o}=t.dataset;try{t.classList.add("is-loading"),t.setAttribute("href","#");const e=await s.post(`/posts/${n}/translations/${o}`,{});t.classList.remove("is-loading");const r=e[o];r&&(t.classList.add("is-status-draft"),t.setAttribute("href",r.edit_link),t.setAttribute("target","_blank"),window.open(r.edit_link,"_blank"))}catch(e){console.log(e)}}e&&e.forEach((e=>{e.addEventListener("click",t)}))})()})),n(522),document.addEventListener("DOMContentLoaded",(()=>{(()=>{function e(e){const{checked:t}=e.currentTarget,n=e.currentTarget.getAttribute("value"),o=e.currentTarget.closest(".menu-item-settings").querySelector(`label [name*="[${n}]"]`);o&&(o.closest("label").style.display=t?"":"none")}function t(e){const t=e.currentTarget;t.closest(".menu-item").querySelectorAll(".bogo-menu-titles label:not(.has-fixed-placeholder) input").forEach((e=>{e.setAttribute("placeholder",t.value)}))}function n(e){const{value:t}=e.currentTarget;e.currentTarget.closest("label").classList.toggle("is-empty",!t)}document.body.classList.contains("nav-menus-php")&&(document.querySelectorAll('.menu-item .bogo-locale-options input[type="checkbox"]').forEach((t=>{t.addEventListener("change",e)})),document.querySelectorAll(".edit-menu-item-title").forEach((e=>{e.addEventListener("input",t)})),document.querySelectorAll(".bogo-field input").forEach((e=>{e.addEventListener("change",n)})))})()})),n(806),document.addEventListener("DOMContentLoaded",(()=>{(()=>{if(!document.body.classList.contains("term-php"))return;const e=document.querySelector("input#name");function t(e){const{value:t}=e.currentTarget;e.currentTarget.closest("label").classList.toggle("is-empty",!t)}e&&e.addEventListener("input",(function(e){const t=e.currentTarget;document.querySelectorAll(".bogo-term-names .bogo-field input").forEach((e=>{e.setAttribute("placeholder",t.value)}))})),document.querySelectorAll(".bogo-field input").forEach((e=>{e.addEventListener("change",t)}))})()})),n(948)}()}();