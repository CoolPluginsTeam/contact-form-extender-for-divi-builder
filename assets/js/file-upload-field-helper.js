(function ($) {
    'use strict';
    /**
     * CFEFD_FileUpload Class
     * Handles the frontend logic for the file upload field.
     */
    class CFEFD_FileUpload {
        constructor() {
            // Global registries for the current form session
            this.uploadedFiles = {};   // { fieldId: ["file1.png", "file2.pdf"] }
            this.filesValidation = {}; // { fieldName: ["file1.png"] }

            this.init();
        }

        /**
         * Initialize the class
         */
        init() {
            this.bindEvents();
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Use delegation to support dynamically added forms/fields
            $("body").on("change", ".cfefd_file_input", this.handleFileSelect.bind(this));
            $("body").on("click", ".cfefd_delete_file", this.handleFileRemove.bind(this));
            $("body").on("click", ".cfefd_file_upload_button", this.handleUploadButtonClick.bind(this));
            $("body").on("click", ".cfefd_dismiss_error", this.handleErrorDismiss.bind(this));
        }

        /**
         * Handle upload button click
         */
        handleUploadButtonClick(e) {
            e.preventDefault();
            const btn = $(e.currentTarget);
            btn.siblings('.cfefd_file_input').trigger('click');
        }

        /**
         * Disable form buttons during processing
         */
        disableForm(form) {
            form.find("button[type=submit]").prop("disabled", true).addClass("is-uploading");
        }

        /**
         * Enable form buttons after processing
         */
        enableForm(form) {
            form.find("button[type=submit]").prop("disabled", false).removeClass("is-uploading");
        }

        /**
         * Display inline errors
         */
        showErrors(errors, fieldWrapper) {
            const errorList = $("<ul class='cool-file-errors'></ul>");
            // errors.forEach(err => {
            //     errorList.append("<li>" + err + "<span class='cfefd_dismiss_error et-pb-icon'>&#x4d;</span></li>");
            // });
            errorList.append("<li>" + errors[0] + "<span class='cfefd_dismiss_error et-pb-icon'>&#x4d;</span></li>");

            fieldWrapper.find(".cool-file-errors").remove();
            fieldWrapper.append(errorList);
        }

        /**
         * Handle error dismissal
         */
        handleErrorDismiss(e) {
            const btn = $(e.currentTarget);
            const li = btn.closest("li");
            const ul = li.closest("ul");

            li.fadeOut(300, function () {
                $(this).remove();
                if (ul.find("li").length === 0) {
                    ul.remove();
                }
            });
        }

        /**
         * Reset progress description text
         */
        resetDescription(fieldId) {
            const desc = $("#cfefd_accepted_files_desc_" + fieldId);
            desc.text(desc.data("description"));
        }

        /**
         * Handle file selection event
         */
        handleFileSelect(e) {
            const fileInput = $(e.currentTarget);
            const form = fileInput.closest("form");
            const fieldWrapper = fileInput.closest(".et_pb_contact_field");

            const files = fileInput[0].files;
            const fieldId = fileInput.data("field-id");
            const fieldName = fileInput.attr("name");

            const limit = parseInt(fileInput.data("limit"), 10);
            const maxSize = parseInt(fileInput.data("size"), 10);
            const maxSizeFormatted = fileInput.data("size-formatted");

            const fileToken = form.find(`input[name="${fieldName}_file_token"]`).val();

            // Initialize registries for this field if not exists
            if (!this.uploadedFiles[fieldId]) {
                this.uploadedFiles[fieldId] = [];
            }
            if (!this.filesValidation[fieldName]) {
                this.filesValidation[fieldName] = [];
            }

            let errors = [];

            // Validate count
            if (files.length + this.uploadedFiles[fieldId].length > limit) {
                errors.push(`You can upload only ${limit} file(s).`);
            }

            // Validate individual files
            for (let file of files) {
                if (file.size >= maxSize) {
                    errors.push(`${file.name} exceeds ${maxSizeFormatted} limit.`);
                }
                // Check against currently uploaded files (using the name we stored)
                if (this.filesValidation[fieldName].includes(file.name)) {
                    errors.push(`${file.name} is already uploaded.`);
                }
            }

            if (errors.length) {
                this.showErrors(errors, fieldWrapper);
                fileInput.val("");
                return;
            }

            // Proceed to upload
            this.uploadFiles({
                files,
                form,
                fieldWrapper,
                fieldId,
                fieldName,
                fileToken,
                fileInput
            });
        }

        /**
         * Perform AJAX upload
         */
        uploadFiles(data) {
            const { files, form, fieldWrapper, fieldId, fieldName, fileToken, fileInput } = data;
            const self = this;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append(i, files[i]);
            }

            formData.append("action", "cfefd_upload_file");
            formData.append("_wpnonce", DiviContactFormExtender.ajaxNonce);
            formData.append("token", fileToken);

            const fileList = fieldWrapper.find(".cfefd_files_list");
            const hiddenField = form.find(`input[name="${fieldName}"].cool_hidden_original`);

            $.ajax({
                url: DiviContactFormExtender.ajaxURL,
                type: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,

                xhr: function () {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            $("#cfefd_accepted_files_desc_" + fieldId)
                                .text(`Uploading ${percent}%`);
                        }
                    });
                    return xhr;
                },

                beforeSend: function () {
                    self.disableForm(form);
                    fieldWrapper.find(".cool-file-errors").remove();
                },

                success: function (response) {
                    if (!response.success || !response.data.success.length) {
                        // Extract error messages
                        const errs = response.data && response.data.errors
                            ? response.data.errors.map(err => err.message)
                            : ["Unknown error occurred."];
                        self.showErrors(errs, fieldWrapper);
                        return;
                    }

                    // Append successful uploads
                    response.data.success.forEach(file => {
                        self.filesValidation[fieldName].push(file.tmp_name);
                        self.uploadedFiles[fieldId].push(file.name);

                        const html = self.renderFileItem(file, fieldId, fieldName);
                        fileList.append(html);
                    });

                    let fileLenght = self.uploadedFiles[fieldId]?.length
                    if (fileLenght) {
                        fieldWrapper.find('.cfefd_file_chosen_desc').text('You have ' + fileLenght + ' file(s) selected')
                    }
                    // Update hidden original input
                    hiddenField.val(self.uploadedFiles[fieldId].join(","));
                },

                complete: function () {
                    self.enableForm(form);
                    self.resetDescription(fieldId);
                    fileInput.val("");
                }
            });
        }

        /**
         * Render HTML for a single file item
         */
        renderFileItem(file, fieldId, fieldName) {
            return `
                <span class="cfefd_file" data-id="${file.name}">
                    <a href="${file.url}" class="cfefd_file_name" download>${file.tmp_name}</a>
                    <span class="cfefd_file_size">(${file.size})</span>
                    <span class="et-pb-icon cfefd_delete_file"
                        data-field-id="${fieldId}"
                        data-field-name="${fieldName}"
                        data-file-name="${file.name}"
                        data-file-tmp-name="${file.tmp_name}">
                        &#x4d;
                    </span>
                </span>
            `;
        }

        /**
         * Handle file removal click
         */
        handleFileRemove(e) {
            const btn = $(e.currentTarget);
            const fileName = btn.data("file-name");
            const tmpName = btn.data("file-tmp-name");

            if (!confirm(`Remove file: ${tmpName}?`)) {
                return;
            }

            this.removeFile(btn);
        }

        /**
         * Perform AJAX file removal
         */
        removeFile(btn) {
            const self = this;
            const form = btn.closest("form");
            const fieldWrapper = btn.closest(".et_pb_contact_field");
            const fieldId = btn.data("field-id");
            const fieldName = btn.data("field-name");
            const fileName = btn.data("file-name");
            const tmpName = btn.data("file-tmp-name");

            $.ajax({
                url: DiviContactFormExtender.ajaxURL,
                type: "POST",
                dataType: "json",
                data: {
                    action: "cfefd_remove_file",
                    _wpnonce: DiviContactFormExtender.ajaxNonce,
                    file_name: fileName
                },

                beforeSend: function () {
                    self.disableForm(form);
                },

                success: function (response) {
                    if (!response.success) {
                        alert("Could not remove file.");
                        return;
                    }

                    // Remove from UI
                    btn.closest(".cfefd_file").remove();

                    // Remove from memory
                    if (self.uploadedFiles[fieldId]) {
                        self.uploadedFiles[fieldId] = self.uploadedFiles[fieldId].filter(
                            name => name !== fileName
                        );
                    }
                    if (self.filesValidation[fieldName]) {
                        self.filesValidation[fieldName] = self.filesValidation[fieldName].filter(
                            f => f !== tmpName
                        );
                    }

                    // Update hidden field
                    if (self.uploadedFiles[fieldId]) {
                        form.find(`input[name="${fieldName}"].cool_hidden_original`)
                            .val(self.uploadedFiles[fieldId].join(","));
                    }

                    let fileLenght = self.uploadedFiles[fieldId]?.length
                    if (fileLenght) {
                        fieldWrapper.find('.cfefd_file_chosen_desc').text('You have ' + fileLenght + ' file(s) selected')
                    } else {
                        fieldWrapper.find('.cfefd_file_chosen_desc').text('No file chosen')
                    }

                },

                complete: function () {
                    self.enableForm(form);
                }
            });
        }
    }

    $(document).ready(function () {
        new CFEFD_FileUpload();
    });

})(jQuery);
