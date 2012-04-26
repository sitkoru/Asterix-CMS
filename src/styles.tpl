

.{container} {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    color: #414042;
    font-family: arial;
    display: none;
    z-index: 999;
}

.{container} div {
    position: relative;
}

.{container} div, .{container} ul, .{container} ol, .{container} li, .{container} h1, .{container} h2, .{container} h3, .{container} h4, .{container} h5, .{container} h6, .{container} pre, .{container} form, .{container} fieldset, .{container} input, .{container} textarea, .{container} p, .{container} blockquote, .{container} th, .{container} td {
    margin: 0;
    padding: 0;
    text-align: left;
}

.{container} table {
    border-collapse: collapse;
    border-spacing: 0;
}

.{container} fieldset, .{container} img {
    border: 0;
}

.{container} address, .{container} caption, .{container} cite, .{container} code, .{container} dfn, .{container} em, .{container} strong, .{container} th, .{container} var {
    font-style: normal;
    font-weight: normal;
}

.{container} ol, .{container} ul {
    list-style: none;
}

.{container} caption, .{container} th {
    text-align: left;
}

.{container} h1, .{container} h2, .{container} h3, .{container} h4, .{container} h5, .{container} h6 {
    font-size: 100%;
    font-weight: normal;
}

.{container} q:before, .{container} q:after {
    content: '';
}

.{container} abbr, .{container} acronym {
    border: 0;
}

.{container} .popupform {
    position: absolute;
    left: 50%;
    width: 400px;
    margin-left: -250px;
    top: 200px;
    background: #f5f5f6;
    padding: 25px 50px 30px 50px;
    border-radius: 14px;
    /*box-shadow: 1px 3px 12px;
    -moz-box-shadow: 1px 3px 12px;
    -webkit-box-shadow: 1px 3px 12px;*/
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); /* Параметры тени */
    -moz-box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); /* Для Firefox */
    -webkit-box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); /* Для Safari и Chrome */
}

.{container} .popupform .close {
    position: absolute;
    width: 15px;
    height: 14px;
    top: 16px;
    right: 20px;
}

.{container} .popupform h1 {
    color: #1094c6;
    font-size: 28px;

    font-weight: normal;
    margin-top: 0;
    padding-top: 0;
    margin-bottom: 20px;
}

.{container} .popupform p {
    font-size: 13px;
    margin-bottom: 25px;
    padding: 0;
    margin-top: 0;

}

.{container} .popupform a {

}

.{container} .popupform fieldset {
    border: none;
    padding: 0;
    margin: 0;
}

.{container} .popupform label {
    display: block;
    color: #414042;
    font-family: Arial;
    font-size: 13px;
    margin-bottom: 3px;
}

.{container} .popupform span.req {
    color: #ff0000;
}

.{container} .popupform div.field .message {
    position: absolute;
    right: 12px;
    top: 0;
    font-size: 13px;
    color: #ff0000;
    font-family: Arial;
    display: none;
}

.{container} .popupform input[type=text], .{container} .popupform select, .{container} .popupform input[type=file], .{container} .popupform textarea {
    border: solid 1px #d1d1d1;
    border-radius: 5px;
    font-size: 16px;
    margin-bottom: 12px;
    padding: 4px 3px;
}

.{container} .popupform div.error input, .{container} .popupform div.error textarea {
    background: #ffe5e5;
}

.{container} .popupform div.error .message {
    display: block;
}

.{container} .popupform input[type=text], .{container} .popupform textarea {
    width: 384px;
}

.{container} .popupform textarea {
    height: 70px;
    margin-bottom: 9px;
}

.{container} .popupform select {
    min-width: 250px;
}

.{container} .popupform input[type=file] {
    width: 310px;
    font-size: 12px;
}

.{container} .popupform a.delete {
    color: #1799d5;
    font-size: 14px;
    font-family: Arial;
    padding-left: 5px;
    position: absolute;
    top: 24px;
    left: 200px;
}

.{container} .popupform span.desc {
    position: absolute;
    top: 10px;
    left: 150px;
    font-size: 11px;
    font-family: Arial;
    color: #414042;
}

.{container} .popupform div.button {
    margin-top: 15px;
}

.{container} .popupform input[type=submit] {
    background: #1094c6;
    width: 136px;
    height: 49px;
    color: #ffffff;
    font-family: arial;
    font-size: 20px;
    border-radius: 8px;
    border: solid 1px #1094c6;
    cursor: pointer;
    text-align: center;
}

.{container} .popupform input[type=submit]:hover {
    background-image: url("../images/widgets/shadow.png");
}

.{container} .popupform .cg {
    margin-top: 12px;
    margin-left: 2px;
    color: #8b8b8c;
    font-family: Arial;
    font-size: 11px;
}

.{container} .popupform .cg a {
    color: #91bcdb;
}

.{container} .popupform .cg a:hover {
    color: #188fd2;
}

.{container} .popupform iframe {
    overflow: hidden;
    border: none;
    height: 40px;
    width: 100%;
    display: none;
}