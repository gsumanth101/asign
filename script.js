function showFacultyOptions() {
    document.getElementById("faculty-options").style.display = "flex";
    document.getElementById("student-options").style.display = "none";
  }
  
  function showStudentOptions() {
    document.getElementById("faculty-options").style.display = "none";
    document.getElementById("student-options").style.display = "flex";
  }
  
  function openPage(url) {
    window.location.href = url;
  }
  