/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (
    $,
    modal
) {
    'use strict';

    return {
        countrySelector     : $('#country_selector'),
        currencySelector    : $('#currency_selector'),
        infoBlock           : $('#country_info_container'),
        defaultSelector     : $('#default_selector'),
        defaultValue        : $('#wm_default'),
        form                : $('#selector-container-form'),
        autoOpen            : false,
        countryData         : [],

        createModal: function () {
            var self = this;

            return modal({
                type                : 'popup',
                modalClass          : 'welcome_mat_container',
                responsive          : true,
                trigger             : '[data-trigger=welcome-mat-trigger]',
                autoOpen            : false,
                buttons             : [],
                closed: function() {
                    self.resetData();
                },
                escapeKey: function() {
                    if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                        this.options.isOpen && this.modal[0] === document.activeElement) {
                        this.closeModal();
                    }
                }
            }, $('#welcome_mat'));
        },

        init : function() {
            var self = this,
            popup = this.createModal();

            this.countrySelector.on('change', function(e) {
                self.onCountryChanged();
            });

            this.defaultSelector.on('click', function(e) {
                self.onDefaultSelector();
            });

            this.form.on('submit', function(e) {
                popup.closeModal();
            });

            if (!localStorage.getItem('welcome-mat-shown')) {
                if (this.autoOpen) {
                    popup.openModal();
                    localStorage.setItem('welcome-mat-shown', true);
                }
            }
        },

        onCountryChanged: function() {
            if (this.currencySelector && this.countryData[this.countrySelector.val()]['currency']) {
                this.currencySelector.val(
                    this.countryData[this.countrySelector.val()]['currency']
                );
            }

            if (this.infoBlock && this.countryData[this.countrySelector.val()]['info']) {
                this.infoBlock.removeClass('_hide');
                this.infoBlock.html(
                    this.countryData[this.countrySelector.val()]['info']
                );
            } else {
                this.infoBlock.addClass('_hide');
                this.infoBlock.html('');
            }
        },

        onDefaultSelector: function() {
            this.defaultValue.val(1);
            this.sendData();
        },

        resetData : function() {
            this.form.trigger('reset');
        },

        sendData : function() {
            var formData = this.form.serialize();

            if (formData) {
                this.form.submit();
            } else {
                console.error('Form not found');
            }
        }
    };
});
