<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Тесты</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createTestDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openTestCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createTestState.name"
                                label="название"
                                @keydown.enter="SendTestCreateRequest(createTestState)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="testCreatingRequest" :disabled="testCreatingRequest"
                   justify-self="center"
                   @click="SendTestCreateRequest(createTestState)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="testsLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка тестов</span>
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
    <v-container v-if="!testsLoading && tests.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Тесты не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!testsLoading && tests.length != 0">
      <v-list-group
          v-for="test in tests"
          :key="test.name"
          v-model="test.activate"
          no-action
          @click="testQuestionsOpen(test)"
      >
        <template v-slot:activator>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>
                <v-badge x-small color="grey darken-1" content="название">
                  {{ test.name }}
                </v-badge>
              </v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog
                  @click:outside="destroyEditor(test)"
                  :retain-focus="false"
                  v-model="test.updateTestHeaderDialog"
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                         @click="(event) => openTestUpdateHeaderDialog(test, event)">
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
                          Заголовок теста
                        </span>
                      </v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                        <v-btn
                            :loading="test.testHeaderUpdating"
                            :disabled="test.testHeaderUpdating"
                            @click="SendUpdateTestHeaderRequest(test)"
                            color="primary"
                            text
                        >
                          <span class="white--text">Save</span>
                        </v-btn>
                        <v-btn
                            icon
                            @click="closeUpdateTestHeaderDialog(test)"
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
                          <div  :id="test.name"></div>
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
                              v-for="block in test.editorData.blocks"
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
              <v-dialog :retain-focus="false" v-model="updateTestDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openTestUpdateDialog(test, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateTestState.name"
                                        label="name"
                                        @keydown.enter="SendTestUpdateRequest(updatedTest, updateTestState)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="testUpdatingRequest"
                           :disabled="testUpdatingRequest"
                           justify-self="center"
                           @click="SendTestUpdateRequest(updatedTest, updateTestState)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="test.deleting"
                     @click="(event) => SendTestDeleteRequest(test, event)">
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
                Вложенные вопросы
              </v-chip>
              <v-spacer></v-spacer>
              <v-dialog :retain-focus="false" v-model="testQuestionsDialogOpened"
                        max-width="900">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                         @click="openTestQuestionsDialog">
                    <v-icon color="green">mdi-plus</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card>
                    <v-container fluid v-if="questionsLoading"
                                 class="pt-6 pb-6 h-50">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 17px;">Загрузка вопросов</span>
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
                    <v-container v-if="!questionsLoading && (questions.length == 0 || getFilteredTestQuestions(lastOpenedTest).length == 0)"
                                 class="pt-16 pb-16">
                      <v-row justify="center"
                             class="text-button text-uppercase">
                        <span style="font-size: 18px;">Вопросы для добавления не найдены</span>
                      </v-row>
                    </v-container>
                    <v-container v-else-if="!questionsLoading && questions.length != 0">
                      <v-list>
                        <v-subheader>Вопросы</v-subheader>
                        <v-list-item
                            v-for="question in getFilteredTestQuestions(lastOpenedTest)"
                            :key="question.id"
                        >
                          <v-list-item-action>
                            <v-checkbox
                                v-model="question.selectedToAdd"></v-checkbox>
                          </v-list-item-action>
                          <v-list-item-content>
                            <div class="mt-2 mb-2">
                              <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                <v-badge x-small color="grey darken-1" content="название">
                                  {{ question.name }}
                                </v-badge>
                              </span>
                            </div>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list>
                      <v-row class="mt-4">
                        <v-btn block :loading="testQuestionsAdding"
                               :disabled="testQuestionsAdding"
                               @click="SendTestQuestionsAddRequest(lastOpenedTest)">
                          Save
                        </v-btn>
                      </v-row>
                    </v-container>
                  </v-card>
                </template>
              </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-container fluid v-if="test.questionsLoading" class="pt-6 pb-6 h-50">
              <v-row justify="center" class="text-button text-uppecase"><span
                  style="font-size: 15px;">Загрузка вопросов</span>
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
            <v-container v-if="!test.questionsLoading && test.questions.length == 0"
                         class="pt-16 pb-16">
              <v-row justify="center" class="text-button text-uppercase">
                <span style="font-size: 15px;">Вопросы не найдены</span>
              </v-row>
            </v-container>
            <v-list v-if="!test.questionsLoading && test.questions.length != 0">
              <v-list-item
                  v-for="question in test.questions"
                  :key="question.id"
              >
                <v-list-item-content>
                  <div class="mt-2 mb-2">
                    <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                      <v-badge x-small color="grey darken-1" content="название">
                        {{ question.name }}
                      </v-badge>
                    </span>
                  </div>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn icon :loading="question.deleting"
                         @click="SendTestQuestionDeleteRequest(test, question)">
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
  name: "v-tests",
  props: ['siteUrl'],
  data: function() {
    return {
      tests: [],
      testsLoading: false,

      questions: [],
      questionsLoading: false,

      createTestState: {
        name: ''
      },
      createTestDialogOpened: false,
      testCreatingRequest: false,

      updateTestDialog: false,
      updateTestState: {
        name: ''
      },
      updatedTest: {},
      testUpdatingRequest: false,

      lastOpenedTest: {},
      testQuestionsDialogOpened: false,
      testQuestionsAdding: false,

      lastUpdateHeaderTest: {},
    }
  },
  methods: {
    sanitizeTest: function (test) {
      return { id: test.id, name: test.name, header: test.header };
    },
    sanitizeQuestion: function (question) {
      return { id: question.id, name: question.name, text: question.text }
    },

    openTestCreateDialog: function () {
      this.createTestDialogOpened = true;
      this.createTestState.name = '';
    },
    SendTestCreateRequest: function (state) {
      let body = {
        newTest: state
      };
      this.testCreatingRequest = true;
      axios.post(`${this.siteUrl}/wp-json/lms/v1/test`, body)
          .then((response) => {
            let createdTest = JSON.parse(response.data).createdTest;
            this.tests.push({
              id: createdTest.id,
              name: createdTest.name,
              header: createdTest.header,
              active: false,
              deleting: false,
              questions: [],
              questionsLoading: false,
              questionsLoaded: false,
              updateTestHeaderDialog: false,
              editor: {},
              editorData: {},
              testHeaderUpdating: false,
            });
            this.testCreatingRequest = false;
            this.createTestDialogOpened = false;
            console.log('creating test success');
            console.log(response);
          })
          .catch((error) => {
            this.testCreatingRequest = false;
            console.log('creating test failed');
            console.log(error.response.data.message);
          });
    },

    SendTestDeleteRequest: function (test, event) {
      event.stopPropagation();
      test.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/test/${test.id}`)
          .then((response) => {
            let deletedTest = JSON.parse(response.data).deletedTest;
            this.tests = this.tests.filter(currentTest => currentTest.id != deletedTest.id);
            test.deleting = false;
            console.log('test deleting success');
            console.log(response);
          })
          .catch((error) => {
            test.deleting = false;
            console.log('test deleting failed');
            console.log(error.response.data.message);
          });
    },

    openTestUpdateDialog: function (test, event) {
      event.stopPropagation();
      this.updateTestState.name = test.name;
      this.updatedTest = test;
    },
    SendTestUpdateRequest: function (test, state) {

      let body = {
        newTest: state
      };
      this.testUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/test/${test.id}`, body)
          .then((response) => {
            let updatedTest = JSON.parse(response.data).updatedTest;
            test.id = updatedTest.id;
            test.name = updatedTest.name;
            this.testUpdatingRequest = false;
            this.updateTestDialog = false;
            console.log('update test success');
            console.log(response);
          })
          .catch((error) => {
            this.testUpdatingRequest = false;
            console.log('update test failed');
            console.log(error.response.data.message);
          });
    },

    openTestQuestionsDialog: function () {
      this.testQuestionsDialogOpened = true;
      if (this.questions.length == 0) {
        this.questionsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/questions`;
        axios.get(route)
            .then((response) => {
              this.questionsLoading = false;
              this.questions = JSON.parse(response.data).questions;
              console.log('questions loading success');
              console.log(response);
            })
            .catch((error) => {
              this.questionsLoading = false;
              console.log('questions loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredTestQuestions: function (test) {
      return this.questions
          .filter(question => !test.questions.map(currentQuestion => currentQuestion.id).includes(question.id))
          .map(question => {
            question.selectedToAdd = false;
            return question;
          });
    },
    SendTestQuestionsAddRequest: function (test) {
      this.testQuestionsAdding = true;
      let sanitizedQuestions = this.questions.filter(question => question.selectedToAdd).map(question => this.sanitizeQuestion(question));
      this.questions.forEach(question => question.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/test/${test.id}/questions`;
      let body = {
        questions: sanitizedQuestions
      };
      axios.post(route, body)
          .then((response) => {
            let addedQuestions = JSON.parse(response.data).addedQuestions.map(question => {
              question.deleting = false;
              return question;
            });
            test.questions.push(...addedQuestions);
            this.testQuestionsAdding = false;
            this.testQuestionsDialogOpened = false;
            console.log('test questions adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.testQuestionsAdding = false;
            console.log('test questions adding failed');
            console.log(error.response.data.message);
          })
    },
    SendTestQuestionDeleteRequest: function (test, question) {
      question.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/test/${test.id}/question/${question.id}`;
      axios.delete(route)
          .then((response) => {
            question.deleting = false;
            let deletedQuestionId = JSON.parse(response.data).deletedQuestionId;
            test.questions = test.questions.filter(currentQuestion => currentQuestion.id !== deletedQuestionId);
            console.log('test question delete success');
            console.log(response);
          })
          .catch((error) => {
            question.deleting = false;
            console.log('test question  delete failed');
            console.log(error.response.data.message);
          });
    },

    testQuestionsOpen : function(test) {
      this.lastOpenedTest = test;
      if (test.questionsLoaded) return;
      test.questionsLoading = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/test/${test.id}/questions`;
      axios.get(route)
          .then((response) => {
            test.questionsLoading = false;
            test.questionsLoaded = true;
            test.questions = JSON.parse(response.data).questions.map(question => {
              question.deleting = false;
              return question;
            });
            console.log('questions loading success');
            console.log(response);
          })
          .catch((error) => {
            test.questionsLoading = false;
            console.log('questions loading failed');
            console.log(error.response.data.message);
          });
    },
    async openTestUpdateHeaderDialog(test, event) {
      event.stopPropagation();
      this.lastUpdateHeaderTest = test;
      test.updateTestHeaderDialog = true;
      test.editorData = JSON.parse(test.header);
      await this.$nextTick();
      const editor = new EditorJS({
        holder: test.name,
        data: test.editorData,
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
      test.editor = editor;
      let filteredBlocks = test.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function() {
      this.lastUpdateHeaderTest.editor.save()
          .then((outputData) => {
            this.lastUpdateHeaderTest.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateHeaderTest.editorData.blocks.filter(block => block.type == 'code');
                  console.log(filteredBlocks);
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (test) {
      test.editor.destroy();
    },
    closeUpdateTestHeaderDialog: function (test){
      this.destroyEditor(test);
      test.updateTestHeaderDialog = false;
    },
    SendUpdateTestHeaderRequest: function (test) {
      test.testHeaderUpdating = true;
      test.editor.save()
          .then(outputData => {
            let body = {
              newHeader: JSON.stringify(outputData)
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
            let route = `${this.siteUrl}/wp-json/lms/v1/test/${test.id}/header`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedTest = JSON.parse(response.data).updatedTest;
                  test.id = updatedTest.id;
                  test.name = updatedTest.name;
                  test.header = updatedTest.header;
                  test.testHeaderUpdating = false;
                  test.updateTestHeaderDialog = false;
                  test.editor.blocks.clear();
                  test.editor.destroy();
                  console.log('test update success');
                  console.log(response);
                })
                .catch((error) => {
                  test.testHeaderUpdating = false;
                  console.log('test update failed');
                  console.log(error.response.data.message);
                });
          })

    },
  },
  mounted() {
    this.testsLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/tests`;
    axios.get(route)
        .then((response) => {
          this.testsLoading = false;
          this.tests = JSON.parse(response.data).tests.map(test => {
            test.active = false;
            test.deleting = false;
            test.questions = [];
            test.questionsLoading = false;
            test.questionsLoaded = false;
            test.updateTestHeaderDialog = false;
            test.editor = {};
            test.editorData = {};
            test.testHeaderUpdating = false;
            return test;
          });
          console.log('tests loading success');
          console.log(response);
        })
        .catch((error) => {
          this.testsLoading = false;
          console.log('test loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>