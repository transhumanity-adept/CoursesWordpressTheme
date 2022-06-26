<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Модули</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createModuleDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openModuleCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createModuleState.moduleName"
                                label="название"
                                @keydown.enter="SendModuleCreateRequest(createModuleState.moduleName)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="moduleCreatingRequest" :disabled="moduleCreatingRequest"
                   justify-self="center"
                   @click="SendModuleCreateRequest(createModuleState.moduleName)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="modulesLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка модулей</span>
      </v-row>
      <v-row justify="center">
        <v-col cols="5">
          <v-progress-linear
              color="orange"
              indeterminate
              rounded
              height="6"
          ></v-progress-linear>
        </v-col>
      </v-row>
    </v-container>
    <v-container v-if="!modulesLoading && modules.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Модули не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!modulesLoading && modules.length != 0">
      <v-list-group
          v-for="module in modules"
          :key="module.name"
          v-model="module.activate"
          no-action
          @click="moduleThemesOpen(module)"
      >
        <template v-slot:activator>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>
                <v-badge x-small color="grey darken-1" content="название">
                  {{ module.name }}
                </v-badge>
              </v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog
                  @click:outside="destroyEditor(module)"
                  :retain-focus="false"
                  v-model="module.updateModuleDescriptionDialog"
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                         @click="(event) => openModuleUpdateDescriptionDialog(module, event)">
                    <v-icon>mdi-file-document-edit-outline</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card
                      flat
                  >
                    <v-toolbar
                        flat
                        color="primary"
                    >
                      <v-toolbar-title>
                        <span
                            class="white--text text-h6 pl-3"
                        >
                          Описание модуля
                        </span>
                      </v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                        <v-btn
                            :loading="module.moduleDescriptionUpdating"
                            :disabled="module.moduleDescriptionUpdating"
                            @click="SendUpdateModuleDescriptionRequest(module)"
                            color="primary"
                            text
                        >
                          <span class="white--text">Save</span>
                        </v-btn>
                        <v-btn
                            icon
                            @click="closeUpdateModuleDescriptionDialog(module)"
                        >
                          <v-icon color="white">mdi-close</v-icon>
                        </v-btn>
                      </v-toolbar-items>
                    </v-toolbar>
                    <v-row class="ma-0 pa-0" style="height: calc(100vh - 64px) !important; background: #eef5fa;">
                      <v-col class="ma-0 pa-0">
                        <v-card class="ml-5 mr-2 mt-5"
                                style="
                                  min-height: calc(100vh - 105px);
                                  max-height: calc(100vh - 105px);
                                  overflow-y: scroll;
                                  line-height: 27px;
                                  font-size: 17px;
                                "
                        >
                          <div  :id="module.name"></div>
                        </v-card>
                      </v-col>
                      <v-col class="ma-0 pa-0">
                        <v-card class="ml-2 mr-5 mt-5"
                                style="
                                  min-height: calc(100vh - 105px);
                                  max-height: calc(100vh - 105px);
                                  overflow-y: scroll;
                                "
                        >
                          <div
                              v-for="block in module.editorData.blocks"
                              :key="block.id"
                              style="
                                max-width: 80%;
                                margin: 0 auto;
                              "
                          >
                            <pre style="font-size: 14px;" v-if="block.type == 'code'" class="line-numbers language-csharp"><code :id="block.id"></code></pre>
                            <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
                              <li v-for="item in block.data.items" v-html="item"></li>
                            </ul>
                            <span v-else-if="block.type == 'header'" v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                            <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text" v-else></p>
                          </div>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-dialog :retain-focus="false" v-model="updateModuleDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openModuleUpdateDialog(module, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateModuleState.newModuleName"
                                        label="name"
                                        @keydown.enter="SendModuleUpdateRequest(updatedModule, updateModuleState.newModuleName)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="moduleUpdatingRequest"
                           :disabled="moduleUpdatingRequest"
                           justify-self="center"
                           @click="SendModuleUpdateRequest(updatedModule, updateModuleState.newModuleName)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="module.deleting"
                     @click="(event) => SendModuleDeleteRequest(module, event)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <v-divider></v-divider>
        <template v-slot:default>
          <v-sheet rounded class="mr-4 ml-4"
                   style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
            <v-toolbar flat>
              <v-chip small color="orange" text-color="white" class="text-button">
                Вложенные темы
              </v-chip>
              <v-spacer></v-spacer>
              <v-dialog :retain-focus="false" v-model="moduleThemesDialogOpened"
                        max-width="900">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                         @click="openModuleThemesDialog">
                    <v-icon color="green">mdi-plus</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card>
                    <v-container fluid v-if="themesLoading"
                                 class="pt-6 pb-6 h-50">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 17px;">Загрузка тем</span>
                      </v-row>
                      <v-row justify="center">
                        <v-col cols="5">
                          <v-progress-linear
                              color="orange"
                              indeterminate
                              rounded
                              height="6"
                          ></v-progress-linear>
                        </v-col>
                      </v-row>
                    </v-container>
                    <v-container v-if="!themesLoading && (themes.length == 0 || getFilteredModuleThemes(lastOpenedModule).length == 0)"
                                 class="pt-16 pb-16">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 18px;">Темы для добавления не найдены</span>
                      </v-row>
                    </v-container>
                    <v-container v-else-if="!themesLoading && themes.length != 0">
                      <v-list>
                        <v-subheader>Темы</v-subheader>
                        <v-list-item
                            v-for="theme in getFilteredModuleThemes(lastOpenedModule)"
                            :key="theme.name"
                        >
                          <v-list-item-action>
                            <v-checkbox
                                v-model="theme.selectedToAdd"></v-checkbox>
                          </v-list-item-action>
                          <v-list-item-content>
                            <div class="mt-2 mb-2">
                              <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                <v-badge x-small color="grey darken-1" content="название">
                                  {{ theme.name }}
                                </v-badge>
                              </span>
                            </div>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list>
                      <v-row class="mt-4">
                        <v-btn block :loading="moduleThemesAdding"
                               :disabled="moduleThemesAdding"
                               @click="SendModuleThemesAddRequest(lastOpenedModule)">
                          Save
                        </v-btn>
                      </v-row>
                    </v-container>
                  </v-card>
                </template>
              </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-container fluid v-if="module.themesLoading" class="pt-6 pb-6 h-50">
              <v-row justify="center" class="text-button text-uppecase"><span
                  style="font-size: 15px;">Загрузка тем</span>
              </v-row>
              <v-row justify="center">
                <v-col cols="5">
                  <v-progress-linear
                      color="orange"
                      indeterminate
                      rounded
                      height="3"
                  ></v-progress-linear>
                </v-col>
              </v-row>
            </v-container>
            <v-container v-if="!module.themesLoading && module.themes.length == 0"
                         class="pt-16 pb-16">
              <v-row justify="center" class="text-button text-uppercase">
                <span style="font-size: 15px;">Темы не найдены</span>
              </v-row>
            </v-container>
            <v-list v-if="!module.themesLoading && module.themes.length != 0">
              <v-list-item
                  v-for="theme in module.themes"
                  :key="theme.name"
              >
                <v-list-item-content>
                  <div class="mt-2 mb-2">
                    <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                      <v-badge x-small color="grey darken-1" content="название">
                        {{ theme.name }}
                      </v-badge>
                    </span>
                  </div>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn icon :loading="theme.deleting"
                         @click="SendModuleThemeDeleteRequest(module, theme)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </v-list-item-action>
              </v-list-item>
            </v-list>
          </v-sheet>
        </template>
      </v-list-group>
    </v-list>
  </v-card>
</template>

<script>
module.exports = {
  name: "v-modules",
  props: ['siteUrl'],
  data: function() {
    return {
      modules: [],
      modulesLoading: false,

      themes: [],
      themesLoading: false,

      createModuleState: {
        moduleName: ''
      },
      createModuleDialogOpened: false,
      moduleCreatingRequest: false,

      updateModuleDialog: false,
      updateModuleState: {
        newModuleName: ''
      },
      updatedModule: {},
      moduleUpdatingRequest: false,

      lastOpenedModule: {},
      moduleThemesDialogOpened: false,
      moduleThemesAdding: false,

      lastUpdateDescriptionModule: {},
    };
  },
  methods: {
    sanitizeModule: function (module) {
      return {id: module.id, name: module.name};
    },
    sanitizeTheme: function (theme) {
      return { id: theme.id, name: theme.name }
    },

    openModuleCreateDialog: function () {
      this.createModuleDialogOpened = true;
      this.createModuleState.moduleName = '';
    },
    SendModuleCreateRequest: function (moduleName) {
      let body = {
        newModule: {name: moduleName}
      };
      this.moduleCreatingRequest = true;
      axios.post(`${this.siteUrl}/wp-json/lms/v1/module`, body)
          .then((response) => {
            let createdModule = JSON.parse(response.data).createdModule;
            this.modules.push({
              id: createdModule.id,
              name: createdModule.name,
              description: createdModule.description,
              active: false,
              deleting: false,
              themes: [],
              themesLoading: false,
              themesLoaded: false,
              updateModuleDescriptionDialog: false,
              editor: {},
              editorData: {},
              moduleDescriptionUpdating: false,
            });
            this.moduleCreatingRequest = false;
            this.createModuleDialogOpened = false;
            console.log('creating module success');
            console.log(response);
          })
          .catch((error) => {
            this.moduleCreatingRequest = false;
            console.log('creating module failed');
            console.log(error.response.data.message);
          });
    },

    SendModuleDeleteRequest: function (module, event) {
      event.stopPropagation();
      module.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/module/${module.id}`)
          .then((response) => {
            let deletedModule = JSON.parse(response.data).deletedModule;
            this.modules = this.modules.filter(currentModule => currentModule.id != deletedModule.id);
            module.deleting = false;
            console.log('module deleting success');
            console.log(response);
          })
          .catch((error) => {
            module.deleting = false;
            console.log('module deleting failed');
            console.log(error.response.data.message);
          });
    },

    openModuleUpdateDialog: function (module, event) {
      event.stopPropagation();
      this.updateModuleState.newModuleName = module.name;
      this.updatedModule = module;
    },
    SendModuleUpdateRequest: function (module, newModuleName) {

      let body = {
        newModule: {name: newModuleName}
      };
      this.moduleUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/module/${module.id}`, body)
          .then((response) => {
            let updatedModule = JSON.parse(response.data).updatedModule;
            module.id = updatedModule.id;
            module.name = updatedModule.name;
            this.moduleUpdatingRequest = false;
            this.updateModuleDialog = false;
            console.log('update module success');
            console.log(response);
          })
          .catch((error) => {
            this.moduleUpdatingRequest = false;
            console.log('update module failed');
            console.log(error.response.data.message);
          });
    },

    openModuleThemesDialog: function () {
      this.moduleThemesDialogOpened = true;
        if (this.themes.length == 0) {
        this.themesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/themes`;
        axios.get(route)
            .then((response) => {
              this.themesLoading = false;
              this.themes = JSON.parse(response.data).themes;
              console.log('themes loading success');
              console.log(response);
            })
            .catch((error) => {
              this.themesLoading = false;
              console.log('themes loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredModuleThemes: function (module) {
      return this.themes
          .filter(theme => !module.themes.map(currentTheme => currentTheme.name).includes(theme.name))
          .map(theme => {
            theme.selectedToAdd = false;
            return theme;
          });
    },
    SendModuleThemesAddRequest: function (module) {
      this.moduleThemesAdding = true;
      let sanitizedThemes = this.themes.filter(theme => theme.selectedToAdd).map(theme => this.sanitizeTheme(theme));
      this.themes.forEach(theme => theme.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/module/${module.id}/themes`;
      let body = {
        themes: sanitizedThemes
      };
      axios.post(route, body)
          .then((response) => {
            let addedThemes = JSON.parse(response.data).addedThemes.map(theme => {
              theme.deleting = false;
              return theme;
            });
            module.themes.push(...addedThemes);
            this.moduleThemesAdding = false;
            this.moduleThemesDialogOpened = false;
            console.log('module themes adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.moduleThemesAdding = false;
            console.log('module themes adding failed');
            console.log(error.response.data.message);
          })
    },
    SendModuleThemeDeleteRequest: function (module, theme) {
      theme.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/module/${module.id}/theme/${theme.id}`;
      axios.delete(route)
          .then((response) => {
            theme.deleting = false;
            let deletedThemeId = JSON.parse(response.data).deletedThemeId;
            module.themes = module.themes.filter(currentTheme => currentTheme.id !== deletedThemeId);
            console.log('module theme delete success');
            console.log(response);
          })
          .catch((error) => {
            theme.deleting = false;
            console.log('module theme delete failed');
            console.log(error.response.data.message);
          });
    },

    moduleThemesOpen : function(module) {
      this.lastOpenedModule = module;
      if (module.themesLoaded) return;
      module.themesLoading = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/module/${module.id}/themes`;
      axios.get(route)
          .then((response) => {
            module.themesLoading = false;
            module.themesLoaded = true;
            module.themes = JSON.parse(response.data).themes.map(theme => {
              theme.deleting = false;
              return theme;
            });
            console.log('themes loading success');
            console.log(response);
          })
          .catch((error) => {
            module.themesLoading = false;
            console.log('themes loading failed');
            console.log(error.response.data.message);
          });
    },
    async openModuleUpdateDescriptionDialog(module, event) {
      event.stopPropagation();
      this.lastUpdateDescriptionModule = module;
      module.updateModuleDescriptionDialog = true;
      module.editorData = JSON.parse(module.description);
      await this.$nextTick();
      const editor = new EditorJS({
        holder: module.name,
        data: module.editorData,
        tools: {
          code: {
            class: CodeTool,
            shortcut: 'CTRL+SHIFT+C',
          },
          header: {
            class: Header,
            shortcut: 'CTRL+SHIFT+H',
          },
          list: {
            class: List,
            inlineToolbar: true,
            config: {
              defaultStyle: 'unordered'
            },
            shortcut: 'CTRL+SHIFT+V',
          }
        },
        onChange: this.editorChanged
      });
      await editor.isReady;
      module.editor = editor;
      let filteredBlocks = module.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function() {
      this.lastUpdateDescriptionModule.editor.save()
          .then((outputData) => {
            this.lastUpdateDescriptionModule.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateDescriptionModule.editorData.blocks.filter(block => block.type == 'code');
                  console.log(filteredBlocks);
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (module) {
      module.editor.destroy();
    },
    closeUpdateModuleDescriptionDialog: function (module){
      this.destroyEditor(module);
      module.updateModuleDescriptionDialog = false;
    },
    SendUpdateModuleDescriptionRequest: function (module) {
      module.moduleDescriptionUpdating = true;
      module.editor.save()
          .then(outputData => {
            let body = {
              newDescription: JSON.stringify(outputData)
                  .replace(/\\n/g, '\\\\n')
                  .replace(/\\"/g, '\\\\"')
                  .replace(/\\t/g, '\\\\t')
                  .replace(/'/g, "\\'")
            };
            let config = {
              headers: {
                'Content-Type': 'application/json'
              }
            };
            let route = `${this.siteUrl}/wp-json/lms/v1/module/${module.id}/description`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedModule = JSON.parse(response.data).updatedModule;
                  module.id = updatedModule.id;
                  module.name = updatedModule.name;
                  module.description = updatedModule.description;
                  module.moduleDescriptionUpdating = false;
                  module.updateModuleDescriptionDialog = false;
                  module.editor.blocks.clear();
                  module.editor.destroy();
                  console.log('module update success');
                  console.log(response);
                })
                .catch((error) => {
                  module.moduleDescriptionUpdating = false;
                  console.log('module update failed');
                  console.log(error.response.data.message);
                });
          })

    },
  },
  mounted() {
    this.modulesLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/modules`;
    axios.get(route)
        .then((response) => {
          this.modulesLoading = false;
          this.modules = JSON.parse(response.data).modules.map(module => {
            module.active = false;
            module.deleting = false;
            module.themes = [];
            module.themesLoading = false;
            module.themesLoaded = false;
            module.updateModuleDescriptionDialog = false;
            module.editor = {};
            module.editorData = {};
            module.moduleDescriptionUpdating = false;
            return module;
          });
          console.log('modules loading success');
          console.log(response);
        })
        .catch((error) => {
          this.modulesLoading = false;
          console.log('modules loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>