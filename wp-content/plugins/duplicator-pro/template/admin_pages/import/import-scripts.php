<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Controllers\ImportPageController;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$packageDeteleConfirm                      = new DUP_PRO_UI_Dialog();
$packageDeteleConfirm->title               = DUP_PRO_U::__('Delete Package?');
$packageDeteleConfirm->wrapperClassButtons = 'dpro-dlg-import-detete-package-btns';
$packageDeteleConfirm->progressOn          = false;
$packageDeteleConfirm->closeOnConfirm      = true;
$packageDeteleConfirm->message             = DUP_PRO_U::__('Are you sure you want to delete the selected package?');
$packageDeteleConfirm->jsCallback          = 'DupPro.ImportManager.removePackage()';
$packageDeteleConfirm->initConfirm();

$packageInvalidName                  = new DUP_PRO_UI_Messages(
    DUP_PRO_U::__(
        '<b>Invalid archive name:</b> The archive name must follow the Duplicator package name pattern' .
        ' e.g. PACKAGE_NAME_[HASH]_[YYYYMMDDHHSS]_archive.zip (or with a .daf extension). ' .
        '<br>Please make sure not to rename the archive after downloading it and try again!'
    ),
    DUP_PRO_UI_Messages::ERROR
);
$packageInvalidName->hide_on_init    = true;
$packageInvalidName->is_dismissible  = true;
$packageInvalidName->auto_hide_delay = 10000;
$packageInvalidName->initMessage();

$packageAlreadyExists                  = new DUP_PRO_UI_Messages(
    DUP_PRO_U::__('Archive file name already exists! <br>Please remove it and try again!'),
    DUP_PRO_UI_Messages::ERROR
);
$packageAlreadyExists->hide_on_init    = true;
$packageAlreadyExists->is_dismissible  = true;
$packageAlreadyExists->auto_hide_delay = 5000;
$packageAlreadyExists->initMessage();

$packageUploaded                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package uploaded'), DUP_PRO_UI_Messages::NOTICE);
$packageUploaded->hide_on_init    = true;
$packageUploaded->is_dismissible  = true;
$packageUploaded->auto_hide_delay = 5000;
$packageUploaded->initMessage();

$packageCancelUpload                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package upload cancelled'), DUP_PRO_UI_Messages::ERROR);
$packageCancelUpload->hide_on_init    = true;
$packageCancelUpload->is_dismissible  = true;
$packageCancelUpload->auto_hide_delay = 5000;
$packageCancelUpload->initMessage();

$packageRemoved                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package removed'), DUP_PRO_UI_Messages::NOTICE);
$packageRemoved->hide_on_init    = true;
$packageRemoved->is_dismissible  = true;
$packageRemoved->auto_hide_delay = 5000;
$packageRemoved->initMessage();

$importChunkSize = ImportPageController::getChunkSize();
?><script>
    jQuery(document).ready(function ($) {
        var uploadFileMessageContent = <?php $tplMng->renderJson('admin_pages/import/import-message-upload-error'); ?>;

        DupPro.ImportManager = {
            uploaderWrapper: $('#dup-pro-import-upload-tabs-wrapper'),
            uploaderDisabler: $('<div>'),
            uploader: $('#dup-pro-import-upload-file'),
            uploaderContent: $('#dup-pro-import-upload-file-content'),
            packageRowTemplate: $('#dup-pro-import-row-template'),
            packageRowNoFoundTemplate: $('#dup-pro-import-available-packages-templates .dup-pro-import-no-package-found'),
            packagesAviable: $('#dpro-pro-import-available-packages'),
            packagesList: $('#dpro-pro-import-available-packages .packages-list'),
            packageRowUploading: null,
            packageRowToDelete: null,
            autoLaunchAfterUpload: false,
            autoLaunchLink: false,
            confirmLaunchLink: false,
            startUpload: false,
            lastUploadsTimes: [],
            debug: true,
            init: function () {
                $('#dup-pro-import-instructions-toggle').click(function () {
                    $('#dup-pro-import-instructions-content').toggle(300);
                })

                DupPro.ImportManager.uploaderWrapper.css('position', 'relative');
                DupPro.ImportManager.uploaderDisabler = $('<div>').css({
                    'position' : 'absolute',
                    'top' : 0,
                    'left' : 0,
                    'width' : '100%',
                    'height' : '100%',
                    'z-index' : '10',
                    'cursor' : 'not-allowed',
                    'display' : 'none'
                });
                DupPro.ImportManager.uploaderDisabler.appendTo(DupPro.ImportManager.uploaderWrapper);

                DupPro.ImportManager.uploader.upload({
                    autoUpload: true,
                    multiple: false,
                    maxSize: <?php echo empty($importChunkSize) ? wp_max_upload_size() : 10737418240; ?>, //100GB get value from upload_max_filesize
                    maxConcurrent: 1,
                    maxFiles: 1,
                    postData: {
                        action: 'duplicator_pro_import_upload',
                        nonce: <?php echo json_encode(wp_create_nonce('duplicator_pro_import_upload')); ?>
                    },
                    chunkSize: <?php echo $importChunkSize; ?>, // This is in kb
                    action: <?php echo json_encode(get_admin_url(null, 'admin-ajax.php')); ?>,
                    chunked: <?php echo empty($importChunkSize) ? 'false' : 'true'; ?>,
                    label: DupPro.ImportManager.uploaderContent.parent().html(),
                    leave: '<?php echo esc_js(DUP_PRO_U::__('You have uploads pending, are you sure you want to leave this page?')); ?>'
                })
                .on("start.upload", DupPro.ImportManager.onStart)
                .on("complete.upload", DupPro.ImportManager.onComplete)
                .on("filestart.upload", DupPro.ImportManager.onFileStart)
                .on("fileprogress.upload", DupPro.ImportManager.onFileProgress)
                .on("filecomplete.upload", DupPro.ImportManager.onFileComplete)
                .on("fileerror.upload", DupPro.ImportManager.onFileError)
                .on("fileerror.chunkerror", DupPro.ImportManager.onChunkError);

                DupPro.ImportManager.uploaderContent.remove();
                DupPro.ImportManager.uploaderContent = $('#dup-pro-import-upload-file #dup-pro-import-upload-file-content');
                DupPro.ImportManager.initPageButtons();
                DupPro.ImportManager.checkMaxUploadedFiles();

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-remove', function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.packageRowToDelete = $(this).closest('.dup-pro-import-package');
                    <?php $packageDeteleConfirm->showConfirm(); ?>
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-package-detail-toggle', function (event) {
                    event.stopPropagation();
                    let button = $(this);
                    let details = button.closest('.dup-pro-import-package').find('.dup-pro-import-package-detail');
                    if (details.hasClass('no-display')) {
                        button.find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
                        details.removeClass('no-display');
                    } else {
                        button.find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
                        details.addClass('no-display');
                    }
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-cancel-upload', function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.abortUpload();
                    <?php $packageCancelUpload->showMessage(); ?>
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-install', function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.confirmLaunchLink = $(this).data('install-url');
                    $('#dup-pro-import-phase-one').addClass('no-display');
                    $('#dup-pro-import-phase-two').removeClass('no-display');
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-import-set-archive-password', function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.setArchivePassword($(this));
                    return false;
                });

                DupPro.ImportManager.initRemoteUpload();
            },
            initRemoteUpload: function () {
                $('#dup-pro-import-remote-upload').click(function () {
                    let uploadUrl = $('#dup-pro-import-remote-url').val();
                    let parsedUrl = null;

                    try {
                        parsedUrl = new URL(uploadUrl);
                    } catch (error) {
                        DupPro.addAdminMessage("Invalid URL", 'error');
                    }

                    let files = [
                        {
                            'name': parsedUrl.pathname.split('/').pop(),
                            'size': -1
                        }
                    ];
                    if (DupPro.ImportManager.onStart(null, files) == false) {
                        DupPro.ImportManager.onComplete(null);
                        return false;
                    }
                    DupPro.ImportManager.onFileStart(null, files[0]);
                    DupPro.ImportManager.remoteUploadCall(uploadUrl, null);
                });
            },
            remoteUploadCall: function (uploadUrl, restoreDownload) {
                Duplicator.Util.ajaxWrapper(
                    {
                        action: 'duplicator_pro_import_remote_download',
                        url: uploadUrl,
                        restoreDownload : (restoreDownload == null ? '' : JSON.stringify(restoreDownload)),
                        nonce: '<?php echo wp_create_nonce('duplicator_pro_remote_download'); ?>'
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        if (DupPro.ImportManager.packageRowUploading == null) {
                            // if row don't exitst the upload is aboted
                            DupPro.ImportManager.onComplete(null);
                            return '';
                        }

                        if (funcData.status == 'complete') {
                            DupPro.ImportManager.onFileComplete(null, uploadUrl, result);
                            DupPro.ImportManager.onComplete(null);
                        } else {
                            DupPro.ImportManager.updateProgress(funcData.remoteChunk.offset, funcData.remoteChunk.fullSize);
                            DupPro.ImportManager.remoteUploadCall(uploadUrl, funcData.remoteChunk);
                        }
                        return '';
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        DupPro.ImportManager.uploadError(result.data.message);
                        DupPro.ImportManager.onComplete(null);
                        return '';
                    }
                );
            },
            setArchivePassword: function (button) {
                let row = button.closest('.dup-pro-import-package');
                let archiveFile = row.data('path');
                let password = row.find('.dup-import-archive-password-request .dup-import-archive-password').val();

                Duplicator.Util.ajaxWrapper(
                    {
                        action: 'duplicator_pro_import_set_archive_password',
                        nonce: '<?php echo wp_create_nonce('duplicator_pro_import_set_archive_password'); ?>',
                        archive: archiveFile,
                        password: password
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        DupPro.ImportManager.packageRowUploading = row;
                        DupPro.ImportManager.onFileComplete(null, archiveFile, result, false);
                        DupPro.ImportManager.onComplete(null);
                        return '';
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        DupPro.addAdminMessage(data.message, 'error', {'hideDelay': 5000});
                        return '';
                    }
                );
            },
            initPageButtons: function () {
                $('.dup-pro-import-view-list').click(function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.updateViewMode('<?php echo ImportPageController::VIEW_MODE_ADVANCED; ?>');
                });

                $('.dup-pro-import-view-single').click(function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.updateViewMode('<?php echo ImportPageController::VIEW_MODE_BASIC; ?>');
                });

                $('.dup-pro-open-help-link').click(function (event) {
                    $('#contextual-help-link').show();
                });

                $('#dup-pro-import-launch-installer-confirm').click(function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.confirmLaunchInstaller();
                });
                
                $('#dup-pro-import-launch-installer-cancel').click(function (event) {
                    event.stopPropagation();
                    DupPro.ImportManager.confirmLaunchLink = false;
                    $('#dup-pro-import-phase-two').addClass('no-display');
                    $('#dup-pro-import-phase-one').removeClass('no-display');
                    return false;
                });

                $('.dup-pro-tabs-wrapper').each(function () {
                    let tabWrapper = $(this);

                    tabWrapper.find('[data-tab-target]').click(function () {
                        let targetId = $(this).data('tab-target');
                        tabWrapper.find('[data-tab-target]').removeClass('active');
                        tabWrapper.find('.tab-content').addClass('no-display');

                        $(this).addClass('active');
                        tabWrapper.find('#' + targetId).removeClass('no-display');
                    });
                });
            },
            confirmLaunchInstaller: function () {
                window.location.href = DupPro.ImportManager.confirmLaunchLink;
                return false;
            },
            onStart: function (e, files)
            {
                DupPro.ImportManager.uploaderDisabler.show();
                DupPro.ImportManager.startUpload = true;
                DupPro.ImportManager.uploader.upload("disable");
                DupPro.ImportManager.autoLaunchLink = false;

                let isValidName = true;
                let alreadyExists = false;

                $.each(files, function (index, value) {
                    if (!DupPro.ImportManager.isValidFileName(value.name)) {
                        isValidName = false;
                    }

                    if (DupPro.ImportManager.isAlreadyExists(value.name)) {
                        alreadyExists = true;
                    }
                });

                /*if (!isValidName) {
                    <?php $packageInvalidName->showMessage(); ?>
                    DupPro.ImportManager.abortUpload();
                    return false;
                }*/

                if (alreadyExists) {
                    <?php $packageAlreadyExists->showMessage(); ?>
                    DupPro.ImportManager.abortUpload();
                    return false;
                }

                return true;
            },
            onComplete: function (e)
            {
                $('#dup-pro-import-remote-url').val('');

                if (DupPro.ImportManager.autoLaunchAfterUpload && DupPro.ImportManager.autoLaunchLink) {
                    document.location.href = DupPro.ImportManager.autoLaunchLink;
                }
                DupPro.ImportManager.checkMaxUploadedFiles();
            },
            onFileStart: function (e, file)
            {
                DupPro.ImportManager.resetUploadTimes();
                DupPro.ImportManager.packagesList.find('.dup-pro-import-no-package-found').remove();
                DupPro.ImportManager.packageRowUploading = DupPro.ImportManager.packageRowTemplate.clone().prependTo(DupPro.ImportManager.packagesList);

                DupPro.ImportManager.packageRowUploading.removeAttr('id');
                DupPro.ImportManager.packageRowUploading.find('.name .text').text(file.name);
                DupPro.ImportManager.packageRowUploading.find('.size').text(Duplicator.Util.humanFileSize(file.size));
                DupPro.ImportManager.packageRowUploading.find('.created').html("<i><?php DUP_PRO_U::_e('loading...'); ?></i>");

                let loader = DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader').removeClass('no-display');
                loader.find('.dup-pro-meter > span').css('width', '0%');
                loader.find('.text').text('0%');
            },
            onFileProgress: function (e, file, percent, eventObj)
            {
                let position = 0;
                if ('currentChunk' in file) {
                    position = file.currentChunk * file.chunkSize;
                } else {
                    if (eventObj.lengthComputable) {
                        position = eventObj.loaded || eventObj.position;
                    } else {
                        position = false;
                    }
                }

                DupPro.ImportManager.updateProgress(position, file.size);
            },
            onFileComplete: function (e, file, response, showMessage = true)
            {
                let result = null;
                if (typeof response === 'string' || response instanceof String) {
                    result = JSON.parse(response);
                } else {
                    result = response;
                }
                DupPro.ImportManager.resetUploadTimes();

                if (result.success == false) {
                    DupPro.ImportManager.uploadError(result.data.message);
                    return;
                }

                DupPro.ImportManager.packageRowUploading.data('path', result.data.funcData.archivePath);
                if (result.data.funcData.isImportable) {
                    DupPro.ImportManager.packageRowUploading.addClass('is-importable');
                    DupPro.ImportManager.packageRowUploading
                            .find('.dup-pro-import-action-install')
                            .prop('disabled', false)
                            .data('install-url', result.data.funcData.installerPageLink);
                    DupPro.ImportManager.autoLaunchLink = result.data.funcData.installerPageLink;
                } else {
                    DupPro.ImportManager.autoLaunchLink = false;
                    DupPro.ImportManager.packageRowUploading.find('.dup-pro-import-action-package-detail-toggle').trigger('click');
                }
                DupPro.ImportManager.packageRowUploading.find('.dup-pro-import-package-detail').html(result.data.funcData.htmlDetails);
                DupPro.ImportManager.packageRowUploading.find('.size').text(Duplicator.Util.humanFileSize(result.data.funcData.archiveSize));
                DupPro.ImportManager.packageRowUploading.find('.created').text(result.data.funcData.created);
                DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader').addClass('no-display');
                DupPro.ImportManager.packageRowUploading.find('.funcs .actions').removeClass('no-display');
                DupPro.ImportManager.packageRowUploading = null;
                if (showMessage) {
                    <?php $packageUploaded->showMessage(); ?>
                }
            },
            onFileError: function (e, file, error)
            {
                if (error === 'abort') {
                    // no message for abort
                    DupPro.ImportManager.uploadError(null);
                } else if (error === 'size') {
                    DupPro.ImportManager.uploadError(<?php echo json_encode(__('The file size exceeds the maximum upload limit.', 'duplicator-pro')); ?>);
                } else {
                    DupPro.ImportManager.uploadError(error);
                }
            },
            getTimeLeft: function (sizeToFinish) {
                if (DupPro.ImportManager.lastUploadsTimes.length < 2) {
                    return false;
                }
                let pos1 = DupPro.ImportManager.lastUploadsTimes[0].pos;
                let time1 = DupPro.ImportManager.lastUploadsTimes[0].time;

                let index = DupPro.ImportManager.lastUploadsTimes.length - 1
                let pos2 = DupPro.ImportManager.lastUploadsTimes[index].pos;
                let time2 = DupPro.ImportManager.lastUploadsTimes[index].time;

                let deltaPos = pos2 - pos1;
                let deltaTime = time2 - time1;

                return deltaTime / deltaPos * sizeToFinish;
            },
            millisecToTime: function (s) {
                if (s <= 0) {
                    return 'loading...';
                }

                var ms = s % 1000;
                s = (s - ms) / 1000;
                var secs = s % 60;
                s = (s - secs) / 60;
                var mins = s % 60;
                var hrs = (s - mins) / 60;

                let result = '';
                if (hrs > 0) {
                    result += ' ' + hrs + ' hr';
                }

                if (mins > 0) {
                    result += ' ' + (mins + 1) + ' min';
                    return result;
                }

                return secs + ' sec';
            },
            resetUploadTimes: function() {
                DupPro.ImportManager.lastUploadsTimes = [];
            },
            addUploadTime: function (postion) {
                if (DupPro.ImportManager.lastUploadsTimes.length > 20) {
                    DupPro.ImportManager.lastUploadsTimes.shift();
                }

                DupPro.ImportManager.lastUploadsTimes.push({
                    'pos': postion,
                    'time': Date.now()
                });
            },
            updateProgress: function (position, total) {
                let percent = 0;

                if (position !== false) {
                    DupPro.ImportManager.addUploadTime(position);
                    percent = Math.round((position / total) * 100 * 10) / 10;
                    percent = Number.isInteger(percent) ? percent + ".0" : percent; 
                }

                DupPro.ImportManager.packageRowUploading.find('.size').text(Duplicator.Util.humanFileSize(total));
                let timeLeft = DupPro.ImportManager.getTimeLeft(total - position);
                let loader = DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader');
                loader.find('.dup-pro-meter > span').css("width", percent + "%");
                loader.find('.text').text(percent + "% - " + DupPro.ImportManager.millisecToTime(timeLeft));
            },
            updateContentMessage: function (icon, line1, line2) {
                DupPro.ImportManager.uploaderContent.find('.message').html('<i class="fas ' + icon + ' fa-sm"></i> ' + line1 + '<br>' + line2);
            },
            uploadError: function(message) {
                DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowUploading);
                DupPro.ImportManager.packageRowUploading = null;

                if (message != null) {
                    DupPro.addAdminMessage(uploadFileMessageContent, 'error', {
                        'hideDelay': 60000,
                        'updateCallback': function (msgNode) {
                            msgNode.find('.import-upload-error-message').text(message);
                        }
                    });
                }
            },
            isAlreadyExists: function (name) {
                let alreadyExists = false;
                DupPro.ImportManager.packagesList.find('tbody .name .text').each(function () {
                    if (name === $(this).text()) {
                        alreadyExists = true;
                    }
                });

                return alreadyExists;
            },
            isValidFileName: function (name) {
                if (!name.match(<?php echo DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN; ?>)) {
                    return false;
                }
                return true;
            },
            abortUpload: function () {
                try {
                    DupPro.ImportManager.uploader.upload("abort");
                } catch (err) {
                    // prevent abort error
                }
                DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowUploading);
                DupPro.ImportManager.packageRowUploading = null;
            },
            removePackage: function () {
                Duplicator.Util.ajaxWrapper(
                    {
                        action: 'duplicator_pro_import_package_delete',
                        path: DupPro.ImportManager.packageRowToDelete.data('path'),
                        nonce: '<?php echo wp_create_nonce('duplicator_pro_import_package_delete'); ?>'
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowToDelete);
                        <?php $packageRemoved->showMessage(); ?>;
                        return '';
                    }
                );
            },
            removeRow: function (row) {
                if (!row) {
                    return;
                }
                row.fadeOut(
                    'fast',
                    function () {
                        row.remove();
                        if (DupPro.ImportManager.packagesList.find('.dup-pro-import-package').length === 0) {
                            DupPro.ImportManager.packageRowNoFoundTemplate.clone().appendTo(DupPro.ImportManager.packagesList);
                        }
                        DupPro.ImportManager.checkMaxUploadedFiles();
                    }
                );
            },
            checkMaxUploadedFiles: function () {
                let limit = 0; // 0 no limit       
                let numPackages = $('.packages-list .dup-pro-import-package').length;

                if ($('#dpro-pro-import-available-packages').hasClass('view-single-item')) {
                    limit = 1;
                }

                if (limit > 0 && numPackages >= limit) {
                    DupPro.ImportManager.uploaderDisabler.show();
                    DupPro.ImportManager.uploader.upload("disable");
                } else {
                    DupPro.ImportManager.uploaderDisabler.hide();
                    DupPro.ImportManager.uploader.upload("enable");
                }
            },
            disableWrapper: function () {
                DupPro.ImportManager.uploaderWrapper
            },
            updateViewMode: function (viewMode) {
                Duplicator.Util.ajaxWrapper(
                    {
                        action: 'duplicator_pro_import_set_view_mode',
                        nonce: '<?php echo wp_create_nonce('duplicator_pro_import_set_view_mode'); ?>',
                        view_mode: viewMode
                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        switch (funcData) {
                            case '<?php echo ImportPageController::VIEW_MODE_ADVANCED; ?>':
                                $('.dup-pro-import-view-single').removeClass('active');
                                $('.dup-pro-import-view-list').addClass('active');
                                $('#dup-pro-basic-mode-message').addClass('no-display');
                                DupPro.ImportManager.packagesAviable.removeClass('view-single-item').addClass('view-list-item');
                                break;
                            case '<?php echo ImportPageController::VIEW_MODE_BASIC; ?>':
                                $('.dup-pro-import-view-list').removeClass('active');
                                $('.dup-pro-import-view-single').addClass('active');
                                $('#dup-pro-basic-mode-message').removeClass('no-display');
                                DupPro.ImportManager.packagesAviable.removeClass('view-list-item').addClass('view-single-item');
                                break;
                            default:
                                throw '<?php DUP_PRO_U::_e('Invalid view mode'); ?>';
                        }
                        DupPro.ImportManager.checkMaxUploadedFiles();
                        return '';

                    },
                    function (result, data, funcData, textStatus, jqXHR) {
                        DupPro.addAdminMessage(data.message, 'error', {'hideDelay': 5000});
                        return '';
                    }
                );
            },
            console: function () {
                if (this.debug) {
                    if (arguments.length > 1) {
                        console.log(arguments[0], arguments[1]);
                    } else {
                        console.log(arguments[0]);
                    }
                }
            }
        };

        // wait form stone init, it's not a great method but for now I haven't found a better one.
        window.setTimeout(DupPro.ImportManager.init, 500);

        $('.dup-pro-import-box.closable').each(function () {
            let box = $(this);
            let title = $(this).find('.box-title');
            let content = $(this).find('.box-content');

            title.click(function () {
                if (box.hasClass('opened')) {
                    box.removeClass('opened').addClass('closed');
                } else {
                    box.removeClass('closed').addClass('opened');
                }
            });
        });
    });
</script>
