// replacement for what was used in moment.js 

function isLeapYear(year) {
  return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
}

function lastDayOfMonth(month, year) {
  if (month == 2 && isLeapYear(year)) {
    return 29;
  } else {
    return [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
  }
}

function padZero(n) {
  if (n < 9) {
    return "0" + n;
  } else {
    return n;
  }
}

function YMDprefix() {
  var now = new Date();
  return now.getFullYear() + "-" + padZero(now.getMonth() + 1) + "-";
}

function firstDayYMD() {
  return YMDprefix() + "01";
}

function lastDayYMD() {
  var now = new Date();
  var thisYear = now.getFullYear();
  var thisMonth = now.getMonth() + 1;
  return YMDprefix() + lastDayOfMonth(thisMonth, thisYear);
}
