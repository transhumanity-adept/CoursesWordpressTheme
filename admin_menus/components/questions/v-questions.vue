<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Вопросы</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createQuestionDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openQuestionCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createQuestionState.name"
                                label="название"
                                @keydown.enter="SendQuestionCreateRequest(createQuestionState)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="questionCreatingRequest" :disabled="questionCreatingRequest"
                   justify-self="center"
                   @click="SendQuestionCreateRequest(createQuestionState)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="questionsLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка вопросов</span>
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
    <v-container v-if="!questionsLoading && questions.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Вопросы не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!questionsLoading && questions.length != 0">
      <v-list-group
          v-for="question in questions"
          :key="question.name"
          v-model="question.active"
          no-action
          @click="questionOptionsOpen(question)"
      >
        <template v-slot:activator>
          <v-list-item>
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
              <v-dialog
                  @click:outside="destroyEditor(question)"
                  :retain-focus="false"
                  v-model="question.updateQuestionTextDialog"
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                         @click="(event) => openQuestionUpdateTextDialog(question, event)">
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
                          Описание курса
                        </span>
                      </v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                        <v-btn
                            :loading="question.questionTextUpdating"
                            :disabled="question.questionTextUpdating"
                            @click="SendUpdateQuestionTextRequest(question)"
                            color="primary"
                            text
                        >
                          <span class="white--text">Save</span>
                        </v-btn>
                        <v-btn
                            icon
                            @click="closeUpdateQuestionTextDialog(question)"
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
                          <div :id="question.name"></div>
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
                              v-for="block in question.editorData.blocks"
                              :key="block.id"
                              style="
                                max-width: 80%;
                                margin: 0 auto;
                              "
                          >
                            <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                 class="line-numbers language-csharp"><code :id="block.id"></code></pre>
                            <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
                              <li v-for="item in block.data.items" v-html="item"></li>
                            </ul>
                            <span v-else-if="block.type == 'header'"
                                  v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
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
              <v-dialog :retain-focus="false" v-model="updateQuestionDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openQuestionUpdateDialog(question, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateQuestionState.name"
                                        label="название"
                                        @keydown.enter="SendQuestionUpdateRequest(updatedQuestion, updateQuestionState)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="questionUpdatingRequest"
                           :disabled="questionUpdatingRequest"
                           justify-self="center"
                           @click="SendQuestionUpdateRequest(updatedQuestion, updateQuestionState)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="question.deleting"
                     @click="(event) => SendQuestionDeleteRequest(question, event)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <v-divider></v-divider>
        <template v-slot:default>
          <v-window v-model="currentWindow">
            <div class="flex flex-row align-center justify-center" style="width: 100%; height: 50px; display: flex !important;">
              <div style="display: flex; flex-direction: row; align-items: center;">
                <span :class="currentWindow === 0 ? 'primary--text' : 'secondary--text'" class="text-button">Варианты ответа</span>
                <v-btn icon @click="currentWindow = 0">
                  <v-icon style="font-size: 32px;">mdi-chevron-left</v-icon>
                </v-btn>
              </div>
              <div style="display: flex; flex-direction: row; align-items: center">
                <v-btn icon @click="currentWindow = 1">
                  <v-icon style="font-size: 32px;">mdi-chevron-right</v-icon>
                </v-btn>
                <span :class="currentWindow === 1 ? 'primary--text' : 'secondary--text'" class="text-button">Компетенции</span>
              </div>
            </div>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;
                       border-top-left-radius: 0px !important; border-top-right-radius: 0px !important;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Варианты ответа
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="question.questionOptionsDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openQuestionOptionsDialog(question)">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="optionsLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка вариантов ответа</span>
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
                        <v-container
                            v-if="!optionsLoading && (options.length == 0 || getFilteredQuestionOptions(lastOpenedQuestion).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Варианты ответа для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!optionsLoading && options.length != 0">
                          <v-list>
                            <v-subheader>Варианты ответа</v-subheader>
                            <v-list-item
                                v-for="option in getFilteredQuestionOptions(lastOpenedQuestion)"
                                :key="option.id"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="option.selectedToAdd"></v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <div class="mt-2 mb-2">
                                  <div
                                      v-for="block in JSON.parse(option.value).blocks"
                                      :key="block.id"
                                      class="mt-2 mb-2"
                                  >
                                    <pre style="font-size: 14px;" v-if="block.type == 'code'"
                                         class="line-numbers language-csharp"><code
                                        :id="question.id + '_' + block.id + '_added'"></code></pre>
                                    <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
                                      <li v-for="item in block.data.items" v-html="item"></li>
                                    </ul>
                                    <span v-else-if="block.type == 'header'"
                                          v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                                    <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text" v-else></p>
                                  </div>
                                </div>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="questionOptionsAdding"
                                   :disabled="questionOptionsAdding"
                                   @click="SendQuestionOptionsAddRequest(lastOpenedQuestion)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="question.optionsLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка вариантов ответа</span>
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
                <v-container v-if="!question.optionsLoading && question.options.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Варианты ответа не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!question.optionsLoading && question.options.length != 0">
                  <v-list-item
                      v-for="option in question.options"
                      :key="option.id"
                  >
                    <v-list-item-content>
                      <div
                          v-for="block in JSON.parse(option.value).blocks"
                          :key="block.id"
                          class="mt-2 mb-2"
                          style="padding-right: 100px !important;"
                      >
                        <pre style="font-size: 14px;" v-if="block.type == 'code'"
                             class="line-numbers language-csharp"><code
                            :id="question.id + '_' + block.id + '_view'"></code></pre>
                        <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
                          <li v-for="item in block.data.items" v-html="item"></li>
                        </ul>
                        <span v-else-if="block.type == 'header'"
                              v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
                        <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text" v-else></p>
                      </div>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-badge left x-small color="grey darken-1" content="правильный">
                        <v-checkbox :disabled="option.isRightUpdating" v-model="option.isRightAnswer"
                                    @change="updateQuestionOptionRight(question, option)"></v-checkbox>
                      </v-badge>
                    </v-list-item-action>
                    <v-list-item-action>
                      <v-btn icon :loading="option.deleting"
                             @click="SendQuestionOptionDeleteRequest(question, option)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
            <v-window-item >
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Приобретаемые компетенции
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="question.questionCompetencesDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openQuestionCompetencesDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="question.competencesLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка компетенций</span>
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
                        <v-container
                            v-if="!question.competencesLoading && (competences.length == 0 || getFilteredQuestionCompetences(lastOpenedQuestion).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Компетенции для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!question.competencesLoading && competences.length != 0">
                          <v-list>
                            <v-subheader>Компетенции</v-subheader>
                            <v-list-item
                                v-for="competence in getFilteredQuestionCompetences(lastOpenedQuestion)"
                                :key="competence.name"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="competence.selectedToAdd"></v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <div class="mt-2 mb-2">
                                  <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                    <v-badge x-small color="grey darken-1" content="название">
                                      {{ competence.name }}
                                    </v-badge>
                                  </span>
                                </div>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="questionCompetencesAdding"
                                   :disabled="questionCompetencesAdding"
                                   @click="SendQuestionCompetencesAddRequest(lastOpenedQuestion)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="question.competencesLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка компетенций</span>
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
                <v-container v-if="!question.competencesLoading && question.competences.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Компетенции не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!question.competencesLoading && question.competences.length != 0">
                  <v-list-item
                      v-for="competence in question.competences"
                      :key="competence.name"
                  >
                    <v-list-item-content>
                      <div class="mt-2 mb-2">
                        <span
                            style="font-family: Roboto, sans-serif; font-size: 16px;">
                          <v-badge x-small color="grey darken-1"
                                   content="название">
                            {{ competence.name }}
                          </v-badge>
                        </span>
                      </div>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon :loading="competence.deleting"
                             @click="SendQuestionCompetenceDeleteRequest(question, competence)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
          </v-window>
        </template>
      </v-list-group>
    </v-list>
  </v-card>
</template>

<script>
module.exports = {
  name: "v-questions",
  props: ['siteUrl'],
  data: function () {
    return {
      options: [],
      optionsLoading: false,

      questions: [],
      questionsLoading: false,
      createQuestionState: {
        name: '',
      },
      createQuestionDialogOpened: false,
      questionCreatingRequest: false,
      lastOpenedQuestion: {},

      updateQuestionDialog: false,
      updateQuestionState: {
        name: '',
      },
      questionUpdatingRequest: false,

      questionOptionsAdding: false,

      competences: [],
      competencesLoading: false,
      questionCompetencesAdding: false,

      lastUpdateTextQuestion: {},

      currentWindow: 0,
    };
  },
  methods: {
    sanitizeOption: function (option) {
      return {id: option.id, value: option.value};
    },
    openQuestionCreateDialog: function () {
      this.createQuestionDialogOpened = true;
      this.createQuestionState.name = '';
    },
    SendQuestionCreateRequest: function (questionState) {
      this.questionCreatingRequest = true;
      let body = {
        newQuestion: questionState
      };
      axios.post(`${this.siteUrl}/wp-json/lms/v1/question`, body)
          .then((response) => {
            let createdQuestion = JSON.parse(response.data).createdQuestion;
            this.questions.push({
              id: createdQuestion.id,
              name: createdQuestion.name,
              text: createdQuestion.text,
              options: [],
              optionsLoading: false,
              optionsLoaded: false,
              active: false,
              deleting: false,
              questionOptionsDialogOpened: false,
              competences: [],
              competencesLoading: false,
              competencesLoaded: false,
              questionCompetencesDialogOpened: false,
              updateQuestionTextDialog: false,
              questionTextUpdating: false,
              isRightAnswer: false,
              editor: {},
              editorData: {},
            });
            this.questionCreatingRequest = false;
            this.createQuestionDialogOpened = false;
            console.log('creating question success');
            console.log(response);
          })
          .catch((error) => {
            this.questionCreatingRequest = false;
            console.log('creating question failed');
            console.log(error.response.data.message);
          });
    },

    questionOptionsOpen: function (question) {
      this.lastOpenedQuestion = question;
      this.currentWindow = 0;
      if (!question.optionsLoaded) {
        question.optionsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/options`;
        axios.get(route)
            .then((response) => {
              question.optionsLoading = false;
              question.optionsLoaded = true;
              question.options = JSON.parse(response.data).options.map(option => {
                option.deleting = false;
                option.isRightUpdating = false;
                return option;
              });
              console.log('options loading success');
              console.log(response);
            })
            .catch((error) => {
              question.optionsLoading = false;
              console.log('options loading failed');
              console.log(error.response.data.message);
            });
      }
      if (!question.competencesLoaded) {
        question.competencesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/competences`;
        axios.get(route)
            .then((response) => {
              question.competencesLoading = false;
              question.competencesLoaded = true;
              question.competences = JSON.parse(response.data).competences.map(competence => {
                competence.deleting = false;
                return competence;
              });
              console.log('competences loading success');
              console.log(response);
            })
            .catch((error) => {
              question.competencesLoading = false;
              console.log('competences loading failed');
              console.log(error.response.data.message);
            });
      }
    },

    openQuestionUpdateDialog: function (question, event) {
      event.stopPropagation();
      this.updateQuestionState.name = question.name;
      this.updatedQuestion = question;
    },
    SendQuestionUpdateRequest: function (question, updateQuestionState) {
      let body = {
        newQuestion: updateQuestionState
      };
      this.questionUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/question/${question.id}`, body)
          .then((response) => {
            let updatedQuestion = JSON.parse(response.data).updatedQuestion;
            question.id = updatedQuestion.id;
            question.name = updatedQuestion.name;
            question.text = updatedQuestion.text;
            this.questionUpdatingRequest = false;
            this.updateQuestionDialog = false;
            console.log('update question success');
            console.log(response);
          })
          .catch((error) => {
            this.questionUpdatingRequest = false;
            console.log('update question failed');
            console.log(error.response.data.message);
          });
    },

    updateQuestionOptionRight: function (question, option) {
      option.isRightUpdating = true;
      let body = {
        newRight: option.isRightAnswer,
      };
      let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/option/${option.id}/isright`;
      axios.put(route, body)
          .then((response) => {
            let updatedRight = JSON.parse(response.data).updatedRight;
            option.isRightAnswer = updatedRight;
            option.isRightUpdating = false;
            console.log('update option right success');
            console.log(response);
          })
          .catch((error) => {
            option.isRightUpdating = false;
            console.log('update option right failed');
            console.log(error.response.data.message);
          });
    },

    SendQuestionDeleteRequest: function (question, event) {
      event.stopPropagation();
      question.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/question/${question.id}`)
          .then((response) => {
            let deletedQuestion = JSON.parse(response.data).deletedQuestion;
            this.questions = this.questions.filter(currentQuestion => currentQuestion.id != deletedQuestion.id);
            question.deleting = false;
            console.log('question deleting success');
            console.log(response);
          })
          .catch((error) => {
            question.deleting = false;
            console.log('question deleting failed');
            console.log(error.response.data.message);
          });
    },

    openQuestionOptionsDialog: function (question) {
      question.questionOptionsDialogOpened = true;
      if (this.options.length == 0) {
        this.optionsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/options`;
        axios.get(route)
            .then((response) => {
              this.optionsLoading = false;
              this.options = JSON.parse(response.data).options;
              console.log('options loading success');
              console.log(response);
              this.$nextTick()
                  .then(() => {
                    this.getFilteredQuestionOptions(question).forEach(option => {
                      console.log(option);
                      let filteredBlocks = JSON.parse(option.value).blocks.filter(block => block.type == 'code');
                      for (const filteredBlock of filteredBlocks) {
                        document.getElementById(question.id + '_' + filteredBlock.id + '_added').innerHTML = filteredBlock.data.code;
                      }
                    });
                    Prism.highlightAll();
                  });
            })
            .catch((error) => {
              this.optionsLoading = false;
              console.log('options loading failed');
              console.log(error.response.data.message);
            });
      } else {
        console.log(question.id);
        this.$nextTick()
            .then(() => {
              this.getFilteredQuestionOptions(question).forEach(option => {
                console.log(option.value);
                let filteredBlocks = JSON.parse(option.value).blocks.filter(block => block.type == 'code');
                for (const filteredBlock of filteredBlocks) {
                  console.log(question.id + '_' + filteredBlock.id + '_added');
                  document.getElementById(question.id + '_' + filteredBlock.id + '_added').innerHTML = filteredBlock.data.code;
                }
              });
              Prism.highlightAll();
            });
      }
    },
    getFilteredQuestionOptions: function (question) {
      return this.options
          .filter(option => !question.options.map(currentOption => currentOption.id).includes(option.id))
          .map(option => {
            option.selectedToAdd = false;
            return option;
          });
    },
    SendQuestionOptionsAddRequest: function (question) {
      this.questionOptionsAdding = true;
      let sanitizedOptions = this.options.filter(option => option.selectedToAdd).map(option => this.sanitizeOption(option));
      this.options.forEach(option => option.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/options`;
      let body = {
        options: sanitizedOptions
      };
      axios.post(route, body)
          .then((response) => {
            let addedOptions = JSON.parse(response.data).addedOptions.map(option => {
              option.deleting = false;
              option.isRightAnswer = false;
              option.isRightUpdating = false;
              return option;
            });
            question.options.push(...addedOptions);
            this.questionOptionsAdding = false;
            question.questionOptionsDialogOpened = false;
            console.log('question options adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.questionOptionsAdding = false;
            console.log('question options adding failed');
            console.log(error.response.data.message);
          })
    },
    SendQuestionOptionDeleteRequest: function (question, option) {
      option.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/option/${option.id}`;
      axios.delete(route)
          .then((response) => {
            option.deleting = false;
            let deletedOptionId = JSON.parse(response.data).deletedOptionId;
            question.options = question.options.filter(currentOption => currentOption.id !== deletedOptionId);
            console.log('question option delete success');
            console.log(response);
          })
          .catch((error) => {
            option.deleting = false;
            console.log('question option delete failed');
            console.log(error.response.data.message);
          });
    },

    sanitizeCompetence: function(competence) {
      return {id: competence.id, name: competence.name};
    },
    openQuestionCompetencesDialog: function (question) {
      question.questionCompetencesDialogOpened = true;
      if (this.competences.length == 0) {
        this.competencesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/competences`;
        axios.get(route)
            .then((response) => {
              this.competencesLoading = false;
              this.competences = JSON.parse(response.data).competences;
              console.log('competences loading success');
              console.log(response);
            })
            .catch((error) => {
              this.competencesLoading = false;
              console.log('competences loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredQuestionCompetences: function (question) {
      return this.competences
          .filter(competence => !question.competences.map(currentCompetence => currentCompetence.name).includes(competence.name))
          .map(competence => {
            competence.selectedToAdd = false;
            return competence;
          });
    },
    SendQuestionCompetencesAddRequest: function (question) {
      this.questionCompetencesAdding = true;
      let sanitizedCompetences = this.competences.filter(competence => competence.selectedToAdd).map(competence => this.sanitizeCompetence(competence));
      this.competences.forEach(competence => competence.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/competences`;
      let body = {
        competences: sanitizedCompetences
      };
      axios.post(route, body)
          .then((response) => {
            let addedCompetences = JSON.parse(response.data).addedCompetences.map(competence => {
              competence.deleting = false;
              return competence;
            });
            question.competences.push(...addedCompetences);
            this.questionCompetencesAdding = false;
            question.questionCompetencesDialogOpened = false;
            console.log('question competences adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.questionCompetencesAdding = false;
            console.log('question competences adding failed');
            console.log(error.response.data.message);
          })
    },
    SendQuestionCompetenceDeleteRequest: function (question, competence) {
      competence.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/competence/${competence.id}`;
      axios.delete(route)
          .then((response) => {
            competence.deleting = false;
            let deletedCompetenceId = JSON.parse(response.data).deletedCompetenceId;
            question.competences = question.competences.filter(currentCompetence => currentCompetence.id !== deletedCompetenceId);
            console.log('question competence delete success');
            console.log(response);
          })
          .catch((error) => {
            competence.deleting = false;
            console.log('question competence delete failed');
            console.log(error.response.data.message);
          });
    },

    async openQuestionUpdateTextDialog(question, event) {
      event.stopPropagation();
      this.lastUpdateTextQuestion = question;
      question.updateQuestionTextDialog = true;
      question.editorData = JSON.parse(question.text);
      await this.$nextTick();
      const editor = new EditorJS({
        holder: question.name,
        data: question.editorData,
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
      question.editor = editor;
      let filteredBlocks = question.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function () {
      this.lastUpdateTextQuestion.editor.save()
          .then((outputData) => {
            this.lastUpdateTextQuestion.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateTextQuestion.editorData.blocks.filter(block => block.type == 'code');
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (question) {
      question.editor.destroy();
    },
    closeUpdateQuestionTextDialog: function (question) {
      this.destroyEditor(question);
      question.updateQuestionTextDialog = false;
    },
    SendUpdateQuestionTextRequest: function (question) {
      question.questionTextUpdating = true;
      question.editor.save()
          .then(outputData => {

            let body = {
              newText: JSON.stringify(outputData)
                  .replace(/\\n/g, '\\\\n')
                  .replace(/\\"/g, '\\\\"')
                  .replace(/\\t/g, '\\\\t')
                  .replace(/'/g, "\\'")
            };
            let config = {
              headers: {
                'Text-Type': 'application/json'
              }
            };
            let route = `${this.siteUrl}/wp-json/lms/v1/question/${question.id}/text`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedQuestion = JSON.parse(response.data).updatedQuestion;
                  question.id = updatedQuestion.id;
                  question.name = updatedQuestion.name;
                  question.text = updatedQuestion.text;
                  question.questionTextUpdating = false;
                  question.updateQuestionTextDialog = false;
                  question.editor.blocks.clear();
                  question.editor.destroy();
                  console.log('question update success');
                  console.log(response);
                })
                .catch((error) => {
                  question.questionTextUpdating = false;
                  console.log('question update failed');
                  console.log(error.response.data.message);
                });
          })

    },
  },
  computed: {
    test: function () {
      return this.lastOpenedQuestion.options;
    },
  },
  watch: {
    test(options) {
      this.$nextTick()
          .then(() => {
            console.log(options);
            options.forEach(option => {
              console.log(option.value);
              let filteredBlocks = JSON.parse(option.value).blocks.filter(block => block.type == 'code');
              for (const filteredBlock of filteredBlocks) {
                document.getElementById(this.lastOpenedQuestion.id + '_' + filteredBlock.id + '_view').innerHTML = filteredBlock.data.code;
              }
            });
            Prism.highlightAll();
          });
    }
  },
  mounted() {
    this.questionsLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/questions`;
    axios.get(route)
        .then((response) => {
          this.questionsLoading = false;
          this.questions = JSON.parse(response.data).questions.map(question => {
            question.active = false;
            question.deleting = false;
            question.options = [];
            question.optionsLoading = false;
            question.optionsLoaded = false;
            question.competences = [];
            question.competencesLoading = false;
            question.competencesLoaded = false;
            question.questionCompetencesDialogOpened = false;
            question.updateQuestionTextDialog = false;
            question.questionTextUpdating = false;
            question.editor = {};
            question.editorData = {};
            question.questionOptionsDialogOpened = false;
            return question;
          });
          console.log('questions loading success');
          console.log(response);
        })
        .catch((error) => {
          this.questionsLoading = false;
          console.log('questions loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>