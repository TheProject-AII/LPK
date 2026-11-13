/**
  * vee-validate v4.11.3
  * (c) 2023 Abdelrahman Awad
  * @license MIT
  */function i(r){return!!(r==null||r===""||Array.isArray(r)&&r.length===0)}const f=r=>{if(i(r))return!0;const t=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;return Array.isArray(r)?r.every(n=>t.test(String(n))):t.test(String(r))};function e(r){return r==null}function s(r){return Array.isArray(r)&&r.length===0}const u=r=>e(r)||s(r)||r===!1?!1:!!String(r).trim().length;export{f as e,u as r};
