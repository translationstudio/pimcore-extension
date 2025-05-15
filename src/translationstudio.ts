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

declare var pimcore: any;
declare var Ext: any;

pimcore.registerNS("pimcore.plugin.IdmediaTranslationstudioBundle");

// @ts-ignore: duplicate in public folder
const languageStore = Ext.create("Ext.data.Store", {
  fields: [
    { name: "name", type: "string" },
    { name: "quota", type: "string" },
    { name: "connector", type: "string" },
    { name: "source", type: "string" },
    { name: "targets", type: "auto" },
    { name: "machine", type: "boolean" },
  ],
  data: [],
});

interface LanguageOption {
  boxLabel: string;
  name: string;
  inputValue: string;
  checked: boolean;
}

const URL_TRANSLATIONSTUDIO_LOGO =
  "https://www.translationstudio.tech/assets/logos/ts_logo_farbig.svg";

class IdmediatranslationstudioBundleBase {
  static showSuccessMessage(message: string, title = "Success") {
    Ext.Msg.alert(title, message);
  }

  static showErrorMessage(message: string, title = "Error") {
    Ext.Msg.alert(title, message);
  }
}

class IdmediatranslationstudioBundleSettings {
  onReady() {
    const toolbar = pimcore.globalmanager.get("layout_toolbar");
    if (typeof toolbar === "undefined" || !toolbar) return;

    Ext.Ajax.request({
      url: "/admin/get-user-info",
      method: "POST",
    });

    toolbar.settingsMenu.add({
      text: "translationstudio",
      iconCls: "translationstudio-icon",
      handler: this.__onHandlePimcoreReadyLicense.bind(this),
    });
  }

  __createLicenseWindow() {
    const window = new Ext.Window({
      title: "translationstudio settings",
      width: 800,
      height: 600,
      layout: {
        type: "vbox",
        align: "center",
        justify: "center",
      },
      bodyStyle: "background-color: white;",
      items: [
        {
          xtype: "container",
          layout: {
            type: "hbox",
            align: "center",
            pack: "center",
          },

          bodyStyle: "background-color: white;",
          items: [
            {
              xtype: "image",
              src: URL_TRANSLATIONSTUDIO_LOGO,
              width: 400,
              height: 195,
              alt: "",
              margin: "10 0 10 0",
            },
          ],
        },
        {
          xtype: "container",
          layout: {
            type: "vbox",
          },
          margin: "10 0 0 0",
          items: [
            {
              xtype: "label",
              value: "Lizenz",
              html: "<span style='color: black; font-size: 14px; font-weight: normal;'>translationstudio license</span>",
              margin: "0 0 10 0",
            },
            {
              xtype: "textarea",
              name: "licenseField",
              width: 670,
              itemId: "licenseField",
              border: 2,
              fieldStyle: "border: 2px solid __ea4443;",
            },
          ],
        },
        {
          xtype: "container",
          layout: {
            type: "vbox",
          },
          width: 670,
          items: [
            {
              xtype: "displayfield",
              value:
                "You can create or revoke a license at account.translationstdio.tech <a href='https://account.translationstudio.tech' target='_blank'>account.translationstudio.tech</a> an.",
              anchor: "100%",
              margin: "0 0 10 0",
            },
            {
              xtype: "label",
              value: "Lizenz",
              html: "<span style='color: black; font-size: 14px; font-weight: normal;'>Authorize incoming translationstudio conection against this access key</span>",
              margin: "0 0 10 0",
            },
          ],
        },
        {
          xtype: "container",
          layout: {
            type: "vbox",
            align: "middle",
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
              fieldStyle: "border: 2px solid __ea4443;",
            },
            {
              xtype: "button",
              text: '<img src="/bundles/idmediatranslationstudio/img/key.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Generate key',
              width: 150,
              handler: function () {
                const pthis: any = this;
                const apiField = pthis.up("container").down("#apiField");
                if (apiField)
                  IdmediatranslationstudioBundleSettings.__generateAndAddApiKey(
                    apiField
                  );
              },
            },
          ],
        },
      ],
      listeners: {
        afterrender: function () {
          const pthis: any = this;
          const licenseField = pthis.down("#licenseField");
          const apiField = pthis.down("#apiField");

          Ext.Ajax.request({
            url: "/get-license",
            method: "GET",
            success: function (response: any) {
              licenseField.setValue(Ext.decode(response.responseText).license);
            },
            failure: function () {
              apiField.setValue("No license saved yet.");
            },
          });
          Ext.Ajax.request({
            url: "/get-api",
            method: "GET",
            success: function (response: any) {
              apiField.setValue(Ext.decode(response.responseText).api);
            },
            failure: function () {
              apiField.setValue("No API key generated yet.");
            },
          });
        },
      },
      buttons: [
        {
          text: "Cancel",
          handler: () => window.close(),
        },
        {
          xtype: "component",
          flex: 1,
        },
        {
          flex: 0.2,
          text: '<img src="/bundles/idmediatranslationstudio/img/save.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Save settings',
          handler: function () {
            const licenseValue = window
              .down('textarea[name="licenseField"]')
              ?.getValue();

            Ext.Ajax.request({
              url: "/save-license",
              method: "POST",
              params: {
                license: licenseValue,
              },
              success: () =>
                IdmediatranslationstudioBundleBase.showSuccessMessage(
                  "Lizenz wurde gespeichert."
                ),
              failure: () =>
                IdmediatranslationstudioBundleBase.showErrorMessage(
                  "Lizenz konnte nicht gespeichert werden."
                ),
            });
          },
        },
      ],
    });

    return window;
  }

  static __generateAndAddApiKey(apiField: any) {
    Ext.Ajax.request({
      url: "/create-api",
      method: "POST",
      success: function () {
        Ext.Ajax.request({
          url: "/get-api",
          method: "GET",
          success: function (response: any) {
            apiField.setValue(Ext.decode(response.responseText).api);
          },
        });
      },
      failure: () =>
        IdmediatranslationstudioBundleBase.showErrorMessage(
          "API konnte nicht gespeichert werden."
        ),
    });
  }

  __onHandlePimcoreReadyLicense() {
    const pimcoreWindow = this.__createLicenseWindow();
    pimcoreWindow.show();
  }
}

class IdmediatranslationstudioBundleTranslationRequest extends IdmediatranslationstudioBundleBase {
  static __getLanguageOptions() {
    const languageOptions: LanguageOption[] = [];
    if (languageStore.getCount() === 0) return languageOptions;

    const firstRecordId = languageStore.getAt(0).get("id");
    languageStore.each(function (record: any) {
      languageOptions.push({
        boxLabel: record.get("name"),
        name: "selectedLanguage",
        inputValue: record.get("id"),
        checked: record.get("id") === firstRecordId,
      });
    });

    return languageOptions;
  }

  static __updateTranslationContainer(selectedId: any) {
    const selectedRecord = selectedId
      ? null
      : languageStore.findRecord("id", selectedId);
    if (selectedRecord) {
      const machine = selectedRecord.get("machine");
      const translationContainer = Ext.getCmp("machineContainer");

      if (translationContainer) {
        translationContainer.setHidden(machine === true);
      }
    }
  }

  static __transformLanguageData(language: any) {
    return language.targets.map((target: any) => ({
      source: language.source,
      target: target,
      connector: language.connector,
    }));
  }

  __showWindowWithoutLanguageOptions() {
    const window = new Ext.Window({
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
          cls: "floating-logo",
        },
        {
          xtype: "component",
          html: "No translation settings available. Please check your license.",
          padding: "10",
          style: "color: red; font-weight: bold;",
        },
      ],
      buttons: [
        {
          text: "Cancel",
          margin: "0 0 0 5",
          handler: () => window.close(),
        },
      ],
    });

    window.show();
  }

  __onShowWindowRequest(ele: any, languageOptions: LanguageOption[]) {
    if (languageOptions.length === 0) return;

    const firstRecordId = languageStore.getAt(0).get("id");
    const window = new Ext.Window({
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
          cls: "floating-logo",
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
              top: 10,
            },
            {
              xtype: "radiogroup",
              name: "translationOption",
              padding: "0 10 10 10",
              columns: 1,
              vertical: true,
              items: languageOptions,
              listeners: {
                change: function (_radioGroup: any, selectedValue: any) {
                  IdmediatranslationstudioBundleTranslationRequest.__updateTranslationContainer(
                    selectedValue.selectedLanguage
                  );
                },
              },
            },
            {
              id: "machineContainer",
              items: [
                {
                  xtype: "component",
                  padding: "0 10",
                  html: "Translation Urgency:",
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
                      checked: true,
                    },
                    {
                      boxLabel: "Normal translation (quota based)",
                      name: "isUrgent",
                      inputValue: 1,
                    },
                  ],
                },
                {
                  xtype: "checkbox",
                  name: "sendNotification",
                  boxLabel:
                    "Send email notification on translation status updates",
                  checked: true,
                  padding: "10 15",
                },
              ],
            },
          ],
        },
      ],
      buttons: [
        {
          text: "Cancel",
          margin: "0 0 0 5",
          handler: () => window.close(),
        },
        {
          xtype: "component",
          flex: 1,
        },
        {
          text: '<img src="/bundles/idmediatranslationstudio/img/submit.png" style="height:18px; vertical-align:middle; margin-right:5px;" />Submit translation',
          margin: "0 10 0 0",
          flex: 0.7,
          handler: () =>
            IdmediatranslationstudioBundleTranslationRequest.__handleTranslationRequestSubmit(
              ele,
              window
            ),
        },
      ],
    });

    Ext.defer(function () {
      IdmediatranslationstudioBundleTranslationRequest.__updateTranslationContainer(
        firstRecordId
      );
    }, 50);

    window.show();
  }

  static __isUrgentRequest(window: any) {
    const radioGroupUrgent = window.down('radiogroup[name="isUrgent"]');
    const entry = radioGroupUrgent?.getChecked();
    if (entry && Array.isArray(entry) && entry.length !== 0) {
      const data = radioGroupUrgent.getChecked()[0];
      return data.config?.boxLabel === "Yes";
    }

    return false;
  }

  static __handleTranslationRequestSubmit(ele: any, window: any) {
    const radioGroupLanguage = window.down(
      'radiogroup[name="translationOption"]'
    );
    const notificationCheckbox = window.down(
      'checkbox[name="sendNotification"]'
    );

    if (
      typeof radioGroupLanguage === "undefined" ||
      radioGroupLanguage.getChecked().length === 0
    )
      return;

    const currentObjectId = ele.detail.object.id;
    const selectedRecord = languageStore.findRecord(
      "id",
      radioGroupLanguage.getValue().selectedLanguage
    );
    const machine = selectedRecord.get("machine") === true;
    const isUrgent =
      machine === true ||
      IdmediatranslationstudioBundleTranslationRequest.__isUrgentRequest(
        window
      );

    const jsonLanguages =
      IdmediatranslationstudioBundleTranslationRequest.__transformLanguageData(
        selectedRecord.data
      );

    Ext.Ajax.request({
      url: "/request-translation",
      method: "POST",
      params: {
        id: currentObjectId,
        language: JSON.stringify(jsonLanguages),
        isUrgent: isUrgent,
        machine: machine,
        notification: notificationCheckbox.getValue(),
      },
      success: () => window.close(),
      failure: (response: any) =>
        IdmediatranslationstudioBundleBase.showErrorMessage(
          `Serverfehler: ${response.statusText}`
        ),
    });
  }

  __onShowWindow(ele: any) {
    const languageOptions: LanguageOption[] =
      IdmediatranslationstudioBundleTranslationRequest.__getLanguageOptions();
    if (languageOptions.length === 0) this.__showWindowWithoutLanguageOptions();
    else this.__onShowWindowRequest(ele, languageOptions);
  }

  __loadTranslationLanguages(onResult: Function) {
    Ext.Ajax.request({
      url: "/get-ts-languages",
      method: "GET",
      success: function (response: any) {
        const data = Ext.decode(response.responseText);
        languageStore.loadData(data);
        onResult();
      },
      failure: () =>
        IdmediatranslationstudioBundleBase.showErrorMessage(
          "Fehler beim Abrufen der Sprachen"
        ),
    });
  }

  onReady(ele: any) {
    this.__loadTranslationLanguages(() => {
      ele.detail.object.toolbar.add({
        text: "Translate",
        scale: "small",
        iconCls: "translationstudio-icon",
        iconAlign: "left",
        handler: () => this.__onShowWindow(ele),
      });
    });
  }
}

// @ts-ignore: duplicate in public folder
class IdmediatranslationstudioBundle extends IdmediatranslationstudioBundleBase {
  constructor() {
    super();

    const pSettings = new IdmediatranslationstudioBundleSettings();
    document.addEventListener(
      pimcore.events.pimcoreReady,
      pSettings.onReady.bind(pSettings)
    );

    const pRequest = new IdmediatranslationstudioBundleTranslationRequest();
    document.addEventListener(
      pimcore.events.postOpenObject,
      pRequest.onReady.bind(pRequest)
    );
  }
}

var tstranslationstudioBundlePlugin = new IdmediatranslationstudioBundle();
