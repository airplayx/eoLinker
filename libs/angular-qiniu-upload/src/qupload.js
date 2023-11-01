'use strict';
/**!
 * AngularJS qiniu cloud storage large file upload service with support resumble,progress
 * @author  icattlecoder  <icattlecoder@gmail.com>
 * @version 0.0.1
 */

(function() {
    var angularQFileUpload = angular.module('angularQFileUpload', ['LocalStorageModule']);

    angularQFileUpload.service('$qupload', ['$http', '$q', 'localStorageService',

        function($http, $q, localStorageService) {

            function utf16to8(str) {
                var out, i, len, c;
                out = '';
                len = str.length;
                for (i = 0; i < len; i++) {
                    c = str.charCodeAt(i);
                    if ((c >= 0x0001) && (c <= 0x007F)) {
                        out += str.charAt(i);
                    } else if (c > 0x07FF) {
                        out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
                        out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
                        out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
                    } else {
                        out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
                        out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
                    }
                }
                return out;
            }

            /*
             * Interfaces:
             * b64 = base64encode(data);
             */
            var base64EncodeChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

            function base64encode(str) {
                var out, i, len;
                var c1, c2, c3;
                len = str.length;
                i = 0;
                out = '';
                while (i < len) {
                    c1 = str.charCodeAt(i++) & 0xff;
                    if (i == len) {
                        out += base64EncodeChars.charAt(c1 >> 2);
                        out += base64EncodeChars.charAt((c1 & 0x3) << 4);
                        out += '==';
                        break;
                    }
                    c2 = str.charCodeAt(i++);
                    if (i == len) {
                        out += base64EncodeChars.charAt(c1 >> 2);
                        out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                        out += base64EncodeChars.charAt((c2 & 0xF) << 2);
                        out += '=';
                        break;
                    }
                    c3 = str.charCodeAt(i++);
                    out += base64EncodeChars.charAt(c1 >> 2);
                    out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                    out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
                    out += base64EncodeChars.charAt(c3 & 0x3F);
                }
                return out;
            }
            var defaultsSetting = {
                chunkSize: 1024 * 1024 * 4,
                maxRetryTimes: 3,
                mkblk: [],
                mkfile:[]
            };
            if (window.location.protocol === 'https:') {
                defaultsSetting.mkblk=['https://up.qbox.me/mkblk/','https://up-z1.qbox.me/mkblk/','https://up-z2.qbox.me/mkblk/']
                defaultsSetting.mkfile=['https://up.qbox.me/mkfile/','https://up-z1.qbox.me/mkfile/','https://up-z2.qbox.me/mkfile/']
            } else {
                defaultsSetting.mkblk=['http://up.qiniu.com/mkblk/','http://up-z1.qiniu.com/mkblk/','http://up-z2.qiniu.com/mkblk/']
                defaultsSetting.mkfile=['http://up.qiniu.com/mkfile/','http://up-z1.qiniu.com/mkfile/','http://up-z2.qiniu.com/mkfile/']
            }
            //Is support qiniu resumble upload
            this.support = (
                typeof File !== 'undefined' &&
                typeof Blob !== 'undefined' &&
                typeof FileList !== 'undefined' &&
                (!!Blob.prototype.slice || !!Blob.prototype.webkitSlice || !!Blob.prototype.mozSlice ||
                    false
                )
            );
            if (!this.support) {
                return null;
            }

            var fileHashKeyFunc = function(file) {
                return file.name + file.lastModified + file.size + file.type;
            };

            this.upload = function(config) {
                var deferred = $q.defer();
                var promise = deferred.promise;

                var file = config.file;
                if (!file) {
                    return;
                }

                var fileHashKey = fileHashKeyFunc(file);
                var blockRet = localStorageService.get(fileHashKey);
                if (!blockRet) {
                    blockRet = [];
                }
                var blkCount = (file.size + ((1 << 22) - 1)) >> 22;

                var getChunck = function(file, startByte, endByte) {
                    return file[(file.slice ? 'slice' : (file.mozSlice ? 'mozSlice' : (file.webkitSlice ? 'webkitSlice' : 'slice')))](startByte, endByte);
                };

                var getBlkSize = function(file, blkCount, blkIndex) {

                    if (blkIndex === blkCount - 1) {
                        return file.size - 4194304 * blkIndex;
                    } else {
                        return 4194304;
                    }
                };

                var mkfile = function(file, blockRet) {
                    if (blockRet.length === 0) {
                        return;
                    }
                    var body = '';
                    var b;
                    for (var i = 0; i < blockRet.length - 1; i++) {
                        b = angular.fromJson(blockRet[i]);
                        body += (b.ctx + ',');
                    }
                    b = angular.fromJson(blockRet[blockRet.length - 1]);
                    body += b.ctx;

                    var url = defaultsSetting.mkfile[config.area||0] + file.size;
                    if (config && config.key) {
                        url += ("/key/" + base64encode(utf16to8(config.key)));
                    }
                    $http({
                        url: url,
                        method: 'POST',
                        data: body,
                        headers: {
                            'Authorization': 'UpToken ' + config.token,
                            'Content-Type': 'text/plain'
                        }
                    }).then(function(e) {
                        deferred.resolve(e);
                        localStorageService.remove(fileHashKey);
                    }).catch(function(e) {
                        deferred.reject(e);
                    });
                };
                var xhr;

                var mkblk = function(file, i, retry) {
                    if (i === blkCount) {
                        mkfile(file, blockRet);
                        return;
                    }
                    if (!retry) {
                        deferred.reject('max retried,still failure');
                        return;
                    }
                    var blkSize = getBlkSize(file, blkCount, i);
                    var offset = i * 4194304;
                    var chunck = getChunck(file, offset, offset + blkSize);

                    xhr = new XMLHttpRequest();
                    xhr.open('POST', defaultsSetting.mkblk[config.area||0] + blkSize, true);
                    xhr.setRequestHeader('Authorization', 'UpToken ' + config.token);

                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var nevt = {
                                totalSize: file.size,
                                loaded: evt.loaded + offset
                            };
                            deferred.notify(nevt);
                        }
                    });

                    xhr.upload.onerror = function() {
                        mkblk(config.file, i, --retry);
                    };

                    xhr.onreadystatechange = function(response) {
                        if (response && xhr.readyState === 4 && xhr.status === 200) {
                            if (xhr.status === 200) {
                                blockRet[i] = xhr.responseText;
                                localStorageService.set(fileHashKey, blockRet);
                                mkblk(config.file, ++i, defaultsSetting.maxRetryTimes);
                            } else {
                                mkblk(config.file, i, --retry);
                            }
                        }
                    };
                    xhr.send(chunck);
                };


                mkblk(config.file, blockRet.length, defaultsSetting.maxRetryTimes);
                promise.abort = function() {
                    xhr.abort();
                    localStorageService.remove(fileHashKey);
                };

                promise.pause = function() {
                    xhr.abort();
                };

                return promise;
            };
        }
    ]);
})();

angular.module('angularQFileUpload').directive('ngFileSelect', ['$parse', '$timeout',
    function($parse, $timeout) {
        return function(scope, elem, attr) {
            var fn = $parse(attr['ngFileSelect']);
            if (elem[0].tagName.toLowerCase() !== 'input' || (elem.attr('type') && elem.attr('type').toLowerCase()) !== 'file') {
                var fileElem = angular.element('<input type="file">');
                for (var i = 0; i < elem[0].attributes.length; i++) {
                    fileElem.attr(elem[0].attributes[i].name, elem[0].attributes[i].value);
                }
                if (elem.attr('data-multiple')) fileElem.attr('multiple', 'true');
                fileElem.css('top', 0).css('bottom', 0).css('left', 0).css('right', 0).css('width', '100%').
                css('opacity', 0).css('position', 'absolute').css('filter', 'alpha(opacity=0)');
                elem.append(fileElem);
                if (elem.css('position') === '' || elem.css('position') === 'static') {
                    elem.css('position', 'relative');
                }
                elem = fileElem;
            }
            elem.bind('change', function(evt) {
                var files = [],
                    fileList, i;
                fileList = evt.__files_ || evt.target.files;
                if (fileList !== null) {
                    for (i = 0; i < fileList.length; i++) {
                        files.push(fileList.item(i));
                    }
                }
                $timeout(function() {
                    fn(scope, {
                        $files: files,
                        $event: evt
                    });
                });
            });
        };
    }
]);
