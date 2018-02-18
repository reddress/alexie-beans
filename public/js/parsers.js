"use strict";

///////////
// Parsing

// From Gilgame/parsers.js
function parseAmount(amount_str) {
  amount_str = amount_str.replace(/,/g, ".").trim();

  // separator is first character
  if (amount_str[0] === '.') {
    amount_str = "0" + amount_str;
  }
  
  // no separator
  if (amount_str.indexOf(".") === -1) {
    return parseInt(amount_str) * 100;
  }

  var parts = amount_str.split(".");
  var whole_str = parts[0];
  var cents_str = parts[1];

  if (cents_str.length > 2) {
    cents_str = cents_str.substring(0, 2);
  }

  if (cents_str.length === 0) {
    cents_str = cents_str + "00";
  }
  
  if (cents_str.length === 1) {
    cents_str = cents_str + "0";
  }
  
  return parseInt(whole_str) * 100 + parseInt(cents_str);
}

function getAmountCents() {
  var amountString = amountInput.value;
  return parseAmount(amountString);
}
