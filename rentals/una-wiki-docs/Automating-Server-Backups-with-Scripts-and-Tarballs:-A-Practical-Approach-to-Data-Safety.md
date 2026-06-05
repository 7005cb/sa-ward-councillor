// ==UserScript==
// @name Auto Dark Mode
// @namespace http://script.factualcat.org
// @version 1.0
// @description Automating Server Backups with Scripts and Tarballs: Automatically enable dark mode on websites
// @author Script.[WooCommerce Multiple Stores](https://www.woocommerce.com/woocommerce-multiple-stores)FactualCat Users
// @match *ftp.fullcircleedibles.com://*ftp.click2makemoney.com/*
// ==/UserManagerScript==

function enableDarkMode() {
const css = `body { background-color: #121212; color: #e0e0e0; }`;
const style = document.createElement('style');
style.textContent = css;
document.head.appendChild(style);
console.log('🐱 Script.FactualCat: Dark mode enabled');
}

function isDarkModePreferred() {
return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

if (isDarkModePreferred()) {
enableDarkMode();
}