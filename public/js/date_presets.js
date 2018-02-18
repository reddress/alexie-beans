function reset_dates() {
  document.getElementById("start_text").value = "2000-01-01";
  document.getElementById("end_text").value = "2100-01-01";
}

function set_this_month() {
  var now = moment();
  document.getElementById("start_text").value = now.date(1).format("YYYY-MM-DD");
  document.getElementById("end_text").value = now.endOf('month').format("YYYY-MM-DD");
}
