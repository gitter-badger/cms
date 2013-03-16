$(document).ready(function () {

	var code_editor = CodeMirror.fromTextArea(document.getElementById("code"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: "keep",
        theme: "ambiance",
        tabMode: "shift"
    });

    window.setTimeout(function () {
        code_editor.refresh();
    }, 50);
});