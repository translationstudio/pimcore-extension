var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
/*
Pimcore - translationstudio extension
Copyright (C) 2025 I-D Media GmbH, idmedia.com
 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
pimcore.registerNS("pimcore.plugin.IdmediaTranslationstudioBundle");
// @ts-ignore: duplicate in public folder
var languageStore = Ext.create("Ext.data.Store", {
    fields: [
        { name: "name", type: "string" },
        { name: "quota", type: "string" },
        { name: "connector", type: "string" },
        { name: "source", type: "string" },
        { name: "targets", type: "auto" },
        { name: "machine", type: "boolean" },
    ],
    data: []
});
var URL_TRANSLATIONSTUDIO_LOGO = "https://www.translationstudio.tech/assets/logos/ts_logo_farbig.svg";
var IdmediatranslationstudioBundleBase = /** @class */ (function () {
    function IdmediatranslationstudioBundleBase() {
    }
    IdmediatranslationstudioBundleBase.showSuccessMessage = function (message, title) {
        if (title === void 0) { title = "Success"; }
        Ext.Msg.alert(title, message);
    };
    IdmediatranslationstudioBundleBase.showErrorMessage = function (message, title) {
        if (title === void 0) { title = "Error"; }
        Ext.Msg.alert(title, message);
    };
    return IdmediatranslationstudioBundleBase;
}());
var IdmediatranslationstudioBundleSettings = /** @class */ (function () {
    function IdmediatranslationstudioBundleSettings() {
    }
    IdmediatranslationstudioBundleSettings.prototype.onReady = function () {
        var toolbar = pimcore.globalmanager.get("layout_toolbar");
        if (typeof toolbar === "undefined" || !toolbar)
            return;
        Ext.Ajax.request({
            url: "/admin/get-user-info",
            method: "POST"
        });
        toolbar.settingsMenu.add({
            text: "translationstudio",
            iconCls: "translationstudio-icon",
            handler: this.__onHandlePimcoreReadyLicense.bind(this)
        });
    };
    IdmediatranslationstudioBundleSettings.prototype.__createLicenseWindow = function () {
        var window = new Ext.Window({
            title: "translationstudio settings",
            width: 800,
            height: 600,
            layout: {
                type: "vbox",
                align: "center",
                justify: "center"
            },
            bodyStyle: "background-color: white;",
            items: [
                {
                    xtype: "container",
                    layout: {
                        type: "hbox",
                        align: "center",
                        pack: "center"
                    },
                    bodyStyle: "background-color: white;",
                    items: [
                        {
                            xtype: "image",
                            src: URL_TRANSLATIONSTUDIO_LOGO,
                            width: 400,
                            height: 195,
                            alt: "",
                            margin: "10 0 10 0"
                        },
                    ]
                },
                {
                    xtype: "container",
                    layout: {
                        type: "vbox"
                    },
                    margin: "10 0 0 0",
                    items: [
                        {
                            xtype: "label",
                            value: "Lizenz",
                            html: "<span style='color: black; font-size: 14px; font-weight: normal;'>translationstudio license</span>",
                            margin: "0 0 10 0"
                        },
                        {
                            xtype: "textarea",
                            name: "licenseField",
                            width: 670,
                            itemId: "licenseField",
                            border: 2,
                            fieldStyle: "border: 2px solid __ea4443;"
                        },
                    ]
                },
                {
                    xtype: "container",
                    layout: {
                        type: "vbox"
                    },
                    width: 670,
                    items: [
                        {
                            xtype: "displayfield",
                            value: "You can create or revoke a license at account.translationstdio.tech <a href='https://account.translationstudio.tech' target='_blank'>account.translationstudio.tech</a> an.",
                            anchor: "100%",
                            margin: "0 0 10 0"
                        },
                        {
                            xtype: "label",
                            value: "Lizenz",
                            html: "<span style='color: black; font-size: 14px; font-weight: normal;'>Authorize incoming translationstudio conection against this access key</span>",
                            margin: "0 0 10 0"
                        },
                    ]
                },
                {
                    xtype: "container",
                    layout: {
                        type: "vbox",
                        align: "middle"
                    },
                    margin: "0 0 10 0",
                    items: [
                        {
                            xtype: "textfield",
                            name: "apiField",
                            width: 670,
                            height: 40,
                            itemId: "apiField",
                            readOnly: true,
                            margin: "0 0 10 0",
                            fieldStyle: "border: 2px solid __ea4443;"
                        },
                        {
                            xtype: "button",
                            text: '<img src="/bundles/idmediatranslationstudio/img/key.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Generate key',
                            width: 150,
                            handler: function () {
                                var pthis = this;
                                var apiField = pthis.up("container").down("#apiField");
                                if (apiField)
                                    IdmediatranslationstudioBundleSettings.__generateAndAddApiKey(apiField);
                            }
                        },
                    ]
                },
            ],
            listeners: {
                afterrender: function () {
                    var pthis = this;
                    var licenseField = pthis.down("#licenseField");
                    var apiField = pthis.down("#apiField");
                    Ext.Ajax.request({
                        url: "/get-license",
                        method: "GET",
                        success: function (response) {
                            licenseField.setValue(Ext.decode(response.responseText).license);
                        },
                        failure: function () {
                            apiField.setValue("No license saved yet.");
                        }
                    });
                    Ext.Ajax.request({
                        url: "/get-api",
                        method: "GET",
                        success: function (response) {
                            apiField.setValue(Ext.decode(response.responseText).api);
                        },
                        failure: function () {
                            apiField.setValue("No API key generated yet.");
                        }
                    });
                }
            },
            buttons: [
                {
                    text: "Cancel",
                    handler: function () { return window.close(); }
                },
                {
                    xtype: "component",
                    flex: 1
                },
                {
                    flex: 0.2,
                    text: '<img src="/bundles/idmediatranslationstudio/img/save.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Save settings',
                    handler: function () {
                        var _a;
                        var licenseValue = (_a = window
                            .down('textarea[name="licenseField"]')) === null || _a === void 0 ? void 0 : _a.getValue();
                        Ext.Ajax.request({
                            url: "/save-license",
                            method: "POST",
                            params: {
                                license: licenseValue
                            },
                            success: function () {
                                return IdmediatranslationstudioBundleBase.showSuccessMessage("Lizenz wurde gespeichert.");
                            },
                            failure: function () {
                                return IdmediatranslationstudioBundleBase.showErrorMessage("Lizenz konnte nicht gespeichert werden.");
                            }
                        });
                    }
                },
            ]
        });
        return window;
    };
    IdmediatranslationstudioBundleSettings.__generateAndAddApiKey = function (apiField) {
        Ext.Ajax.request({
            url: "/create-api",
            method: "POST",
            success: function () {
                Ext.Ajax.request({
                    url: "/get-api",
                    method: "GET",
                    success: function (response) {
                        apiField.setValue(Ext.decode(response.responseText).api);
                    }
                });
            },
            failure: function () {
                return IdmediatranslationstudioBundleBase.showErrorMessage("API konnte nicht gespeichert werden.");
            }
        });
    };
    IdmediatranslationstudioBundleSettings.prototype.__onHandlePimcoreReadyLicense = function () {
        var pimcoreWindow = this.__createLicenseWindow();
        pimcoreWindow.show();
    };
    return IdmediatranslationstudioBundleSettings;
}());
var IdmediatranslationstudioBundleTranslationRequest = /** @class */ (function (_super) {
    __extends(IdmediatranslationstudioBundleTranslationRequest, _super);
    function IdmediatranslationstudioBundleTranslationRequest() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    IdmediatranslationstudioBundleTranslationRequest.__getLanguageOptions = function () {
        var languageOptions = [];
        if (languageStore.getCount() === 0)
            return languageOptions;
        var firstRecordId = languageStore.getAt(0).get("id");
        languageStore.each(function (record) {
            languageOptions.push({
                boxLabel: record.get("name"),
                name: "selectedLanguage",
                inputValue: record.get("id"),
                checked: record.get("id") === firstRecordId
            });
        });
        return languageOptions;
    };
    IdmediatranslationstudioBundleTranslationRequest.__updateTranslationContainer = function (selectedId) {
        var selectedRecord = selectedId
            ? null
            : languageStore.findRecord("id", selectedId);
        if (selectedRecord) {
            var machine = selectedRecord.get("machine");
            var translationContainer = Ext.getCmp("machineContainer");
            if (translationContainer) {
                translationContainer.setHidden(machine === true);
            }
        }
    };
    IdmediatranslationstudioBundleTranslationRequest.__transformLanguageData = function (language) {
        return language.targets.map(function (target) { return ({
            source: language.source,
            target: target,
            connector: language.connector
        }); });
    };
    IdmediatranslationstudioBundleTranslationRequest.prototype.__showWindowWithoutLanguageOptions = function () {
        var window = new Ext.Window({
            title: "Requesting Translation",
            width: 500,
            height: 400,
            layout: "absolute",
            cls: "custom-window",
            stateful: true,
            items: [
                {
                    xtype: "image",
                    src: URL_TRANSLATIONSTUDIO_LOGO,
                    width: 200,
                    height: 100,
                    alt: "",
                    cls: "floating-logo"
                },
                {
                    xtype: "component",
                    html: "No translation settings available. Please check your license.",
                    padding: "10",
                    style: "color: red; font-weight: bold;"
                },
            ],
            buttons: [
                {
                    text: "Cancel",
                    margin: "0 0 0 5",
                    handler: function () { return window.close(); }
                },
            ]
        });
        window.show();
    };
    IdmediatranslationstudioBundleTranslationRequest.prototype.__onShowWindowRequest = function (ele, languageOptions) {
        if (languageOptions.length === 0)
            return;
        var firstRecordId = languageStore.getAt(0).get("id");
        var window = new Ext.Window({
            title: "Requesting Translation",
            width: 500,
            height: 400,
            layout: "absolute",
            cls: "custom-window",
            stateful: true,
            items: [
                {
                    xtype: "image",
                    src: URL_TRANSLATIONSTUDIO_LOGO,
                    width: 200,
                    height: 100,
                    alt: "",
                    cls: "floating-logo"
                },
                {
                    xtype: "container",
                    autoScroll: true,
                    height: "100%",
                    padding: "40 0 0 0",
                    items: [
                        {
                            xtype: "component",
                            html: "Translation settings:",
                            padding: "10 10 0 10",
                            top: 10
                        },
                        {
                            xtype: "radiogroup",
                            name: "translationOption",
                            padding: "0 10 10 10",
                            columns: 1,
                            vertical: true,
                            items: languageOptions,
                            listeners: {
                                change: function (_radioGroup, selectedValue) {
                                    IdmediatranslationstudioBundleTranslationRequest.__updateTranslationContainer(selectedValue.selectedLanguage);
                                }
                            }
                        },
                        {
                            id: "machineContainer",
                            items: [
                                {
                                    xtype: "component",
                                    padding: "0 10",
                                    html: "Translation Urgency:"
                                },
                                {
                                    xtype: "radiogroup",
                                    name: "isUrgent",
                                    padding: "0 10",
                                    width: "100%",
                                    items: [
                                        {
                                            boxLabel: "Translate immediately",
                                            name: "isUrgent",
                                            inputValue: 0,
                                            checked: true
                                        },
                                        {
                                            boxLabel: "Normal translation (quota based)",
                                            name: "isUrgent",
                                            inputValue: 1
                                        },
                                    ]
                                },
                                {
                                    xtype: "checkbox",
                                    name: "sendNotification",
                                    boxLabel: "Send email notification on translation status updates",
                                    checked: true,
                                    padding: "10 15"
                                },
                            ]
                        },
                    ]
                },
            ],
            buttons: [
                {
                    text: "Cancel",
                    margin: "0 0 0 5",
                    handler: function () { return window.close(); }
                },
                {
                    xtype: "component",
                    flex: 1
                },
                {
                    text: '<img src="/bundles/idmediatranslationstudio/img/submit.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Submit translation',
                    margin: "0 10 0 0",
                    flex: 0.7,
                    handler: function () {
                        return IdmediatranslationstudioBundleTranslationRequest.__handleTranslationRequestSubmit(ele, window);
                    }
                },
            ]
        });
        Ext.defer(function () {
            IdmediatranslationstudioBundleTranslationRequest.__updateTranslationContainer(firstRecordId);
        }, 50);
        window.show();
    };
    IdmediatranslationstudioBundleTranslationRequest.__isUrgentRequest = function (window) {
        var _a;
        var radioGroupUrgent = window.down('radiogroup[name="isUrgent"]');
        var entry = radioGroupUrgent === null || radioGroupUrgent === void 0 ? void 0 : radioGroupUrgent.getChecked();
        if (entry && Array.isArray(entry) && entry.length !== 0) {
            var data = radioGroupUrgent.getChecked()[0];
            return ((_a = data.config) === null || _a === void 0 ? void 0 : _a.boxLabel) === "Yes";
        }
        return false;
    };
    IdmediatranslationstudioBundleTranslationRequest.__handleTranslationRequestSubmit = function (ele, window) {
        var radioGroupLanguage = window.down('radiogroup[name="translationOption"]');
        var notificationCheckbox = window.down('checkbox[name="sendNotification"]');
        if (typeof radioGroupLanguage === "undefined" ||
            radioGroupLanguage.getChecked().length === 0)
            return;
        var currentObjectId = ele.detail.object.id;
        var selectedRecord = languageStore.findRecord("id", radioGroupLanguage.getValue().selectedLanguage);
        var machine = selectedRecord.get("machine") === true;
        var isUrgent = machine === true ||
            IdmediatranslationstudioBundleTranslationRequest.__isUrgentRequest(window);
        var jsonLanguages = IdmediatranslationstudioBundleTranslationRequest.__transformLanguageData(selectedRecord.data);
        Ext.Ajax.request({
            url: "/request-translation",
            method: "POST",
            params: {
                id: currentObjectId,
                language: JSON.stringify(jsonLanguages),
                isUrgent: isUrgent,
                machine: machine,
                notification: notificationCheckbox.getValue()
            },
            success: function () { return window.close(); },
            failure: function (response) {
                return IdmediatranslationstudioBundleBase.showErrorMessage("Serverfehler: ".concat(response.statusText));
            }
        });
    };
    IdmediatranslationstudioBundleTranslationRequest.prototype.__onShowWindow = function (ele) {
        var languageOptions = IdmediatranslationstudioBundleTranslationRequest.__getLanguageOptions();
        if (languageOptions.length === 0)
            this.__showWindowWithoutLanguageOptions();
        else
            this.__onShowWindowRequest(ele, languageOptions);
    };
    IdmediatranslationstudioBundleTranslationRequest.prototype.__loadTranslationLanguages = function (onResult) {
        Ext.Ajax.request({
            url: "/get-ts-languages",
            method: "GET",
            success: function (response) {
                var data = Ext.decode(response.responseText);
                languageStore.loadData(data);
                onResult();
            },
            failure: function () {
                return IdmediatranslationstudioBundleBase.showErrorMessage("Fehler beim Abrufen der Sprachen");
            }
        });
    };
    IdmediatranslationstudioBundleTranslationRequest.prototype.onReady = function (ele) {
        var _this = this;
        this.__loadTranslationLanguages(function () {
            ele.detail.object.toolbar.add({
                text: "Translate",
                scale: "small",
                iconCls: "translationstudio-icon",
                iconAlign: "left",
                handler: function () { return _this.__onShowWindow(ele); }
            });
        });
    };
    return IdmediatranslationstudioBundleTranslationRequest;
}(IdmediatranslationstudioBundleBase));
// @ts-ignore: duplicate in public folder
var IdmediatranslationstudioBundle = /** @class */ (function (_super) {
    __extends(IdmediatranslationstudioBundle, _super);
    function IdmediatranslationstudioBundle() {
        var _this = _super.call(this) || this;
        var pSettings = new IdmediatranslationstudioBundleSettings();
        document.addEventListener(pimcore.events.pimcoreReady, pSettings.onReady.bind(pSettings));
        var pRequest = new IdmediatranslationstudioBundleTranslationRequest();
        document.addEventListener(pimcore.events.postOpenObject, pRequest.onReady.bind(pRequest));
        return _this;
    }
    return IdmediatranslationstudioBundle;
}(IdmediatranslationstudioBundleBase));
var tstranslationstudioBundlePlugin = new IdmediatranslationstudioBundle();
