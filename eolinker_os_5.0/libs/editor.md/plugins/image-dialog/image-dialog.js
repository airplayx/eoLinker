/*!
 * Image (upload) dialog plugin for Editor.md
 *
 * @file        image-dialog.js
 * @author      pandao
 * @version     1.3.4
 * @updateTime  2015-06-09
 * {@link       https://github.com/pandao/editor.md}
 * @license     MIT
 */

(function () {

    var factory = function (exports) {

        var pluginName = "image-dialog";
        if (exports)
            exports.fn.imageDialog = function () {

                var _this = this;
                var cm = this.cm;
                var lang = this.lang;
                var editor = this.editor;
                var settings = this.settings;
                var cursor = cm.getCursor();
                var selection = cm.getSelection();
                var imageLang = lang.dialog.image;
                var classPrefix = this.classPrefix;
                var iframeName = classPrefix + "image-iframe";
                var dialogName = classPrefix + pluginName,
                    dialog;

                cm.focus();

                var loading = function (show) {
                    var _loading = dialog.find("." + classPrefix + "dialog-mask");
                    _loading[(show) ? "show" : "hide"]();
                };

                if (editor.find("." + dialogName).length < 1) {
                    var guid = (new Date).getTime();
                    var action = settings.imageUploadURL + (settings.imageUploadURL.indexOf("?") >= 0 ? "&" : "?") + "guid=" + guid;

                    if (settings.crossDomainUpload) {
                        action += "&callback=" + settings.uploadCallbackURL + "&dialog_id=editormd-image-dialog-" + guid;
                    }

                    var dialogContent = ((settings.imageUpload) ? "<form  autocomplete=\"off\" action=\"" + action + "\" target=\"" + iframeName + "\" method=\"post\" enctype=\"multipart/form-data\" class=\"" + classPrefix + "form\">" : "<div class=\"" + classPrefix + "form\">") +
                        ((settings.imageUpload) ? "<iframe name=\"" + iframeName + "\" id=\"" + iframeName + "\" guid=\"" + guid + "\"></iframe>" : "") +
                        "<label>" + imageLang.url + "</label>" +
                        "<input autocomplete=\"off\" type=\"text\" data-url />" + (function () {
                            return (settings.imageUpload) ? "<div class=\"" + classPrefix + "file-input\">" +
                                "<input autocomplete=\"off\" type=\"file\" name=\"" + classPrefix + "image-file\" accept=\"image/*\" multiple=\"multiple\"/>" +
                                "<input autocomplete=\"off\" type=\"submit\" value=\"" + imageLang.uploadButton + "\" />" +
                                "</div>" : "";
                        })() +
                        "<br/>" +
                        "<label>" + imageLang.alt + "</label>" +
                        "<input autocomplete=\"off\" type=\"text\" value=\"" + selection + "\" data-alt />" +
                        "<br/>" +
                        "<label>" + imageLang.link + "</label>" +
                        "<input autocomplete=\"off\" type=\"text\" value=\"http://\" data-link />" +
                        "<br/>" +
                        ((settings.imageUpload) ? "</form>" : "</div>");

                    //var imageFooterHTML = "<button class=\"" + classPrefix + "btn " + classPrefix + "image-manager-btn\" style=\"float:left;\">" + imageLang.managerButton + "</button>";

                    dialog = this.createDialog({
                        title: imageLang.title,
                        width: (settings.imageUpload) ? 465 : 380,
                        height: 254,
                        name: dialogName,
                        content: dialogContent,
                        mask: settings.dialogShowMask,
                        drag: settings.dialogDraggable,
                        lockScreen: settings.dialogLockScreen,
                        maskStyle: {
                            opacity: settings.dialogMaskOpacity,
                            backgroundColor: settings.dialogMaskBgColor
                        },
                        buttons: {
                            enter: [lang.buttons.enter, function () {
                                var url = this.find("[data-url]").val();
                                var alt = this.find("[data-alt]").val();
                                var link = this.find("[data-link]").val();

                                if (url === "") {
                                    alert(imageLang.imageURLEmpty);
                                    return false;
                                }

                                var altAttr = (alt !== "") ? " \"" + alt + "\"" : "";

                                if (link === "" || link === "http://") {
                                    cm.replaceSelection("![" + alt + "](" + url + altAttr + ")");
                                } else {
                                    cm.replaceSelection("[![" + alt + "](" + url + altAttr + ")](" + link + altAttr + ")");
                                }

                                if (alt === "") {
                                    cm.setCursor(cursor.line, cursor.ch + 2);
                                }

                                this.hide().lockScreen(false).hideMask();

                                return false;
                            }],

                            cancel: [lang.buttons.cancel, function () {
                                this.hide().lockScreen(false).hideMask();

                                return false;
                            }]
                        }
                    });

                    dialog.attr("id", classPrefix + "image-dialog-" + guid);

                    if (!settings.imageUpload) {
                        return;
                    }

                    var fileInput = dialog.find("[name=\"" + classPrefix + "image-file\"]");

                    fileInput.bind("change", function () {
                        var fileName = fileInput[0].value;
                        console.log(fileName)
                        var isImage = new RegExp("(\\.(" + settings.imageFormats.join("|") + "))$"); // /(\.(webp|jpg|jpeg|gif|bmp|png))$/

                        if (fileName === "") {
                            alert(imageLang.uploadFileEmpty);

                            return false;
                        }

                        if (!isImage.test(fileName)) {
                            alert(imageLang.formatNotAllowed + settings.imageFormats.join(", "));

                            return false;
                        }

                        loading(true);

                        dialog.find("[type=\"submit\"]").trigger("click");
                    });
                    var Qiniu_upload = function (files, length, i) {
                        if (length > i) {
                            $.ajax({
                                type: 'POST',
                                url: '/common/Upload/getPublicUploadToken',
                                dataType: 'json',
                                contentType: false,
                                processData: false,
                                success: function (data) {
                                    var formdata = new FormData()
                                    formdata.append('file', files[i])
                                    formdata.append('key', data.fileName)
                                    formdata.append('token', data.uptoken)
                                    $.ajax({
                                        type: 'POST',
                                        url: "https:" === window.location.protocol ? 'https://up.qbox.me/' : 'http://up.qiniu.com/',
                                        data: formdata,
                                        dataType: 'json',
                                        contentType: false,
                                        processData: false,
                                        success: function (json) {
                                            var oldurl = $('[data-url]').val()
                                            if (oldurl === '') {
                                                $('[data-url]').val('https://data.eolinker.com/' + json.key)
                                            } else {
                                                oldurl = oldurl + ')![](https://data.eolinker.com/' + json.key
                                                $('[data-url]').val(oldurl)
                                            }
                                            i++
                                            Qiniu_upload(files, length, i)
                                        }
                                    })
                                }
                            })

                        } else {
                            $('[name="editormd-image-file"]').val('')
                            loading(false)
                        }
                    }
                    var submitHandler = function () {
                        var files = $('[name="editormd-image-file"]')[0].files
                        if (files.length > 0) {
                            Qiniu_upload(files, files.length, 0)
                        }
                        return false
                    }

                    dialog.find("[type=\"submit\"]").bind("click", submitHandler);
                }

                dialog = editor.find("." + dialogName);
                dialog.find("[type=\"text\"]").val("");
                dialog.find("[type=\"file\"]").val("");
                dialog.find("[data-link]").val("http://");

                this.dialogShowMask(dialog);
                this.dialogLockScreen();
                dialog.show();

            };

    };

    // CommonJS/Node.js
    if (typeof require === "function" && typeof exports === "object" && typeof module === "object") {
        module.exports = factory;
    } else {
        factory(window.editormd);
    }

})();