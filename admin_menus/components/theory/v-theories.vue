<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Теория</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createTheoryDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openTheoryCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createTheoryState.name"
                                label="название"
                                @keydown.enter="SendTheoryCreateRequest(createTheoryState)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="theoryCreatingRequest" :disabled="theoryCreatingRequest"
                   justify-self="center"
                   @click="SendTheoryCreateRequest(createTheoryState)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="theoriesLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка теории</span>
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
    <v-container v-if="!theoriesLoading && theories.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Теория не найдена</span>
      </v-row>
    </v-container>
    <v-list v-if="!theoriesLoading && theories.length != 0">
      <v-list-group
          v-for="theory in theories"
          :key="theory.name"
          v-model="theory.active"
          no-action
          @click="theoryConceptsOpen(theory)"
      >
        <template v-slot:activator>
          <v-list-item>
            <v-list-item-content>
              <div class="mt-2 mb-2">
                <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                  <v-badge x-small color="grey darken-1" content="название">
                    {{ theory.name }}
                  </v-badge>
                </span>
              </div>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog
                  @click:outside="destroyEditor(theory)"
                  :retain-focus="false"
                  v-model="theory.updateTheoryContentDialog"
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                         @click="(event) => openTheoryUpdateContentDialog(theory, event)">
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
                          Содержание теории
                        </span>
                      </v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                        <v-btn
                            :loading="theory.theoryContentUpdating"
                            :disabled="theory.theoryContentUpdating"
                            @click="SendUpdateTheoryContentRequest(theory)"
                            color="primary"
                            text
                        >
                          <span class="white--text">Save</span>
                        </v-btn>
                        <v-btn
                            icon
                            @click="closeUpdateTheoryContentDialog(theory)"
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
                          <div  :id="theory.name"></div>
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
                              v-for="block in theory.editorData.blocks"
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
              <v-dialog :retain-focus="false" v-model="updateTheoryDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openTheoryUpdateDialog(theory, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateTheoryState.name"
                                        label="название"
                                        @keydown.enter="SendTheoryUpdateRequest(updatedTheory, updateTheoryState)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="theoryUpdatingRequest"
                           :disabled="theoryUpdatingRequest"
                           justify-self="center"
                           @click="SendTheoryUpdateRequest(updatedTheory, updateTheoryState)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="theory.deleting"
                     @click="(event) => SendTheoryDeleteRequest(theory, event)">
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
                Требуемые понятия
              </v-chip>
              <v-spacer></v-spacer>
              <v-dialog :retain-focus="false" v-model="theoryConceptsDialogOpened"
                        max-width="900">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                         @click="openTheoryConceptsDialog">
                    <v-icon color="green">mdi-plus</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card>
                    <v-container fluid v-if="conceptsLoading"
                                 class="pt-6 pb-6 h-50">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 17px;">Загрузка понятий</span>
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
                    <v-container v-if="!conceptsLoading && (concepts.length == 0 || getFilteredTheoryConcepts(lastOpenedTheory).length == 0)"
                                 class="pt-16 pb-16">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 18px;">Понятия для добавления не найдены</span>
                      </v-row>
                    </v-container>
                    <v-container v-else-if="!conceptsLoading && concepts.length != 0">
                      <v-list>
                        <v-subheader>Понятия</v-subheader>
                        <v-list-item
                            v-for="concept in getFilteredTheoryConcepts(lastOpenedTheory)"
                            :key="concept.name"
                        >
                          <v-list-item-action>
                            <v-checkbox
                                v-model="concept.selectedToAdd"></v-checkbox>
                          </v-list-item-action>
                          <v-list-item-content>
                            <div class="mt-2 mb-2">
                              <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                <v-badge x-small color="grey darken-1" content="название">
                                  {{ concept.name }}
                                </v-badge>
                              </span>
                              <span class="ml-16" style="font-family: Roboto, sans-serif; font-size: 16px;">
                                <v-badge x-small color="grey darken-1" content="вес">
                                  {{ concept.weight }}
                                </v-badge>
                              </span>
                            </div>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list>
                      <v-row class="mt-4">
                        <v-btn block :loading="theoryConceptsAdding"
                               :disabled="theoryConceptsAdding"
                               @click="SendTheoryConceptsAddRequest(lastOpenedTheory)">
                          Save
                        </v-btn>
                      </v-row>
                    </v-container>
                  </v-card>
                </template>
              </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-container fluid v-if="theory.conceptsLoading" class="pt-6 pb-6 h-50">
              <v-row justify="center" class="text-button text-uppercase"><span
                  style="font-size: 15px;">Загрузка понятий</span>
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
            <v-container v-if="!theory.conceptsLoading && theory.concepts.length == 0"
                         class="pt-16 pb-16">
              <v-row justify="center" class="text-button text-uppercase">
                <span style="font-size: 15px;">Понятия не найдены</span>
              </v-row>
            </v-container>
            <v-list v-if="!theory.conceptsLoading && theory.concepts.length != 0">
              <v-list-item
                  v-for="concept in theory.concepts"
                  :key="concept.name"
              >
                <v-list-item-content>
                  <div class="mt-2 mb-2">
                    <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                      <v-badge x-small color="grey darken-1" content="название">
                        {{ concept.name }}
                      </v-badge>
                    </span>
                    <span class="ml-16" style="font-family: Roboto, sans-serif; font-size: 16px;">
                      <v-badge x-small color="grey darken-1" content="вес">
                        {{ concept.weight }}
                      </v-badge>
                    </span>
                  </div>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn icon :loading="concept.deleting"
                         @click="SendTheoryConceptDeleteRequest(theory, concept)">
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
  name: "v-theory",
  props: ['siteUrl'],
  data: function () {
    return {
      concepts: [],
      conceptsLoading: false,

      theories: [],
      theoriesLoading: false,
      createTheoryState: {
        name: '',
      },
      createTheoryDialogOpened: false,
      theoryCreatingRequest: false,
      lastOpenedTheory: {},

      updateTheoryDialog: false,
      updateTheoryState: {
        name: '',
      },
      updatedTheories: {},
      theoryUpdatingRequest: false,

      theoryConceptsDialogOpened: false,
      theoryConceptsAdding: false,

      lastUpdateContentTheory: {},
    };
  },
  methods: {
    sanitizeConcept: function (concept) {
      return {id: concept.id, name: concept.name, weight: concept.weight};
    },
    openTheoryCreateDialog: function () {
      this.createTheoryDialogOpened = true;
      this.createTheoryState.name = '';
    },
    SendTheoryCreateRequest: function (theoryState) {
      this.theoryCreatingRequest = true;
      let body = {
        newTheory: theoryState
      };
      axios.post(`${this.siteUrl}/wp-json/lms/v1/theory`, body)
          .then((response) => {
            let createdTheory = JSON.parse(response.data).createdTheory;
            this.theories.push({
              id: createdTheory.id,
              name: createdTheory.name,
              content: createdTheory.content,
              concepts: [],
              conceptsLoading: false,
              conceptsLoaded: false,
              active: false,
              deleting: false,
              updateTheoryContentDialog: false,
              theoryContentUpdating: false,
              editor: {},
              editorData: {},
            });
            this.theoryCreatingRequest = false;
            this.createTheoryDialogOpened = false;
            console.log('creating theory success');
            console.log(response);
          })
          .catch((error) => {
            this.theoryCreatingRequest = false;
            console.log('creating theory failed');
            console.log(error.response.data.message);
          });
    },

    theoryConceptsOpen : function(theory) {
      this.lastOpenedTheory = theory;
      if (theory.conceptsLoaded) return;
      theory.conceptsLoading = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}/concepts`;
      axios.get(route)
          .then((response) => {
            theory.conceptsLoading = false;
            theory.conceptsLoaded = true;
            theory.concepts = JSON.parse(response.data).concepts.map(concept => {
              concept.deleting = false;
              return concept;
            });
            console.log('concepts loading success');
            console.log(response);
          })
          .catch((error) => {
            theory.conceptsLoading = false;
            console.log('concepts loading failed');
            console.log(error.response.data.message);
          });
    },

    openTheoryUpdateDialog: function (theory, event) {
      event.stopPropagation();
      this.updateTheoryState.name = theory.name;
      this.updatedTheory = theory;
    },
    SendTheoryUpdateRequest: function (theory, updateTheoryState) {
      let body = {
        newTheory: updateTheoryState
      };
      this.theoryUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}`, body)
          .then((response) => {
            let updatedTheory = JSON.parse(response.data).updatedTheory;
            theory.id = updatedTheory.id;
            theory.name = updatedTheory.name;
            theory.content = updatedTheory.content;
            this.theoryUpdatingRequest = false;
            this.updateTheoryDialog = false;
            console.log('update theory success');
            console.log(response);
          })
          .catch((error) => {
            this.theoryUpdatingRequest = false;
            console.log('update theory failed');
            console.log(error.response.data.message);
          });
    },

    SendTheoryDeleteRequest: function (theory, event) {
      event.stopPropagation();
      theory.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}`)
          .then((response) => {
            let deletedTheory = JSON.parse(response.data).deletedTheory;
            this.theories = this.theories.filter(currentTheory => currentTheory.id != deletedTheory.id);
            theory.deleting = false;
            console.log('theory deleting success');
            console.log(response);
          })
          .catch((error) => {
            theory.deleting = false;
            console.log('theory deleting failed');
            console.log(error.response.data.message);
          });
    },

    openTheoryConceptsDialog: function () {
      this.theoryConceptsDialogOpened = true;
      if (this.concepts.length == 0) {
        this.conceptsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/concepts`;
        axios.get(route)
            .then((response) => {
              this.conceptsLoading = false;
              this.concepts = JSON.parse(response.data).concepts;
              console.log('concepts loading success');
              console.log(response);
            })
            .catch((error) => {
              this.conceptsLoading = false;
              console.log('concepts loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredTheoryConcepts: function (theory) {
      return this.concepts
          .filter(concept => !theory.concepts.map(currentConcept => currentConcept.name).includes(concept.name))
          .map(concept => {
            concept.selectedToAdd = false;
            return concept;
          });
    },
    SendTheoryConceptsAddRequest: function (theory) {
      this.theoryConceptsAdding = true;
      let sanitizedConcepts = this.concepts.filter(concept => concept.selectedToAdd).map(concept => this.sanitizeConcept(concept));
      this.concepts.forEach(concept => concept.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}/concepts`;
      let body = {
        concepts: sanitizedConcepts
      };
      axios.post(route, body)
          .then((response) => {
            let addedConcepts = JSON.parse(response.data).addedConcepts.map(concept => {
              concept.deleting = false;
              return concept;
            });
            theory.concepts.push(...addedConcepts);
            this.theoryConceptsAdding = false;
            this.theoryConceptsDialogOpened = false;
            console.log('theory concepts adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.theoryConceptsAdding = false;
            console.log('theory concepts adding failed');
            console.log(error.response.data.message);
          })
    },
    SendTheoryConceptDeleteRequest: function (theory, concept) {
      concept.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}/concept/${concept.id}`;
      axios.delete(route)
          .then((response) => {
            concept.deleting = false;
            let deletedConceptId = JSON.parse(response.data).deletedConceptId;
            theory.concepts = theory.concepts.filter(currentConcept => currentConcept.id !== deletedConceptId);
            console.log('theory concept delete success');
            console.log(response);
          })
          .catch((error) => {
            concept.deleting = false;
            console.log('theory concept delete failed');
            console.log(error.response.data.message);
          });
    },

    async openTheoryUpdateContentDialog(theory, event) {
      event.stopPropagation();
      this.lastUpdateContentTheory = theory;
      theory.updateTheoryContentDialog = true;
      theory.editorData = JSON.parse(theory.content);
      await this.$nextTick();
      const editor = new EditorJS({
        holder: theory.name,
        data: theory.editorData,
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
      theory.editor = editor;
      let filteredBlocks = theory.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function() {
      this.lastUpdateContentTheory.editor.save()
          .then((outputData) => {
            this.lastUpdateContentTheory.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateContentTheory.editorData.blocks.filter(block => block.type == 'code');
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (theory){
      theory.editor.destroy();
    },
    closeUpdateTheoryContentDialog: function(theory) {
      this.destroyEditor(theory);
      theory.updateTheoryContentDialog = false;
    },
    SendUpdateTheoryContentRequest: function (theory) {
      theory.theoryContentUpdating = true;
      theory.editor.save()
          .then(outputData => {
            let body = {
              newContent: JSON.stringify(outputData)
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
            let route = `${this.siteUrl}/wp-json/lms/v1/theory/${theory.id}/content`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedTheory = JSON.parse(response.data).updatedTheory;
                  theory.id = updatedTheory.id;
                  theory.name = updatedTheory.name;
                  theory.content = updatedTheory.content;
                  theory.theoryContentUpdating = false;
                  theory.updateTheoryContentDialog = false;
                  theory.editor.blocks.clear();
                  theory.editor.destroy();
                  console.log('theory update success');
                  console.log(response);
                })
                .catch((error) => {
                  theory.theoryContentUpdating = false;
                  console.log('theory update failed');
                  console.log(error.response.data.message);
                });
          })

    },
  },
  mounted() {
    this.theoriesLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/theories`;
    axios.get(route)
        .then((response) => {
          this.theoriesLoading = false;
          this.theories = JSON.parse(response.data).theories.map(theory => {
            theory.active = false;
            theory.deleting = false;
            theory.concepts = [];
            theory.conceptsLoading = false;
            theory.conceptsLoaded = false;
            theory.updateTheoryContentDialog = false;
            theory.theoryContentUpdating = false;
            theory.editor = {};
            theory.editorData = {};
            return theory;
          });
          console.log('theories loading success');
          console.log(response);
        })
        .catch((error) => {
          this.theoriesLoading = false;
          console.log('theories loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>