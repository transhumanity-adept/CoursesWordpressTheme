<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Темы</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createThemeDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openThemeCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createThemeState.name"
                                label="name"
                                @keydown.enter="SendThemeCreateRequest(createThemeState)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="themeCreatingRequest" :disabled="themeCreatingRequest"
                   justify-self="center"
                   @click="SendThemeCreateRequest(createThemeState)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="themesLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка тем</span>
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
    <v-container v-if="!themesLoading && themes.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Темы не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!themesLoading && themes.length != 0">
      <v-list-group
          v-for="theme in themes"
          :key="theme.name"
          v-model="theme.activate"
          no-action
          @click="themeEntitiesOpen(theme)"
      >
        <template v-slot:activator>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>
                {{ theme.name }}
              </v-list-item-title>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog :retain-focus="false" v-model="updateThemeDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openThemeUpdateDialog(theme, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateThemeState.name"
                                        label="название"
                                        @keydown.enter="SendThemeUpdateRequest(updatedTheme, updateThemeState)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="themeUpdatingRequest"
                           :disabled="themeUpdatingRequest"
                           justify-self="center"
                           @click="SendThemeUpdateRequest(updatedTheme, updateThemeState)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="theme.deleting"
                     @click="(event) => SendThemeDeleteRequest(theme, event)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-list-item-action>
          </v-list-item>
        </template>
        <v-divider></v-divider>
        <template v-slot:default>
          <v-window v-model="currentWindow">
            <div class="flex flex-row align-center justify-center"
                 style="width: 100%; height: 50px; display: flex !important;">
              <div style="display: flex; flex-direction: row; align-items: center;" class="mr-3">
                <v-btn text @click="currentWindow = 0">
                  <span :class="currentWindow === 0 ? 'primary--text' : 'secondary--text'"
                        class="text-button">Теория</span>
                </v-btn>
              </div>
              <div style="display: flex; flex-direction: row; align-items: center" class="mr-3">
                <v-btn text @click="currentWindow = 1">
                  <span :class="currentWindow === 1 ? 'primary--text' : 'secondary--text'"
                        class="text-button">Тесты</span>
                </v-btn>
              </div>
              <div style="display: flex; flex-direction: row; align-items: center" class="mr-3">
                <v-btn text @click="currentWindow = 2">
                  <span :class="currentWindow === 2 ? 'primary--text' : 'secondary--text'" class="text-button">Компетенции</span>
                </v-btn>
              </div>
              <div style="display: flex; flex-direction: row; align-items: center">
                <v-btn text @click="currentWindow = 3">
                  <span :class="currentWindow === 3 ? 'primary--text' : 'secondary--text'"
                        class="text-button">Понятия</span>
                </v-btn>
              </div>
            </div>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Теория
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="theme.themeTheoriesDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openThemeTheoriesDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="theme.theoriesLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка теории</span>
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
                            v-if="!theme.theoriesLoading && (theories.length == 0 || getFilteredThemeTheories(lastOpenedTheme).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Теория для добавления не найдена</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!theme.theoriesLoading && theories.length != 0">
                          <v-list>
                            <v-subheader>Теория</v-subheader>
                            <v-list-item
                                v-for="theory in getFilteredThemeTheories(lastOpenedTheme)"
                                :key="theory.name"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="theory.selectedToAdd"></v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <div class="mt-2 mb-2">
                                  <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                    <v-badge x-small color="grey darken-1" content="название">
                                      {{ theory.name }}
                                    </v-badge>
                                  </span>
                                </div>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="themeTheoriesAdding"
                                   :disabled="themeTheoriesAdding"
                                   @click="SendThemeTheoriesAddRequest(lastOpenedTheme)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="theme.theoriesLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка теории</span>
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
                <v-container v-if="!theme.theoriesLoading && theme.theories.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Теория не найдена</span>
                  </v-row>
                </v-container>
                <v-list v-if="!theme.theoriesLoading && theme.theories.length != 0">
                  <v-list-item
                      v-for="theory in theme.theories"
                      :key="theory.name"
                  >
                    <v-list-item-content>
                      <div class="mt-2 mb-2">
                        <span
                            style="font-family: Roboto, sans-serif; font-size: 16px;">
                          <v-badge x-small color="grey darken-1"
                                   content="название">
                            {{ theory.name }}
                          </v-badge>
                        </span>
                      </div>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon :loading="theory.deleting"
                             @click="SendThemeTheoryDeleteRequest(theme, theory)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Тесты
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="theme.themeTestsDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openThemeTestsDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="theme.testsLoading"
                                     class="pt-6 pb-6 h-50">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 17px;">Загрузка тестов</span>
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
                            v-if="!theme.testsLoading && (tests.length == 0 || getFilteredThemeTests(lastOpenedTheme).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Тесты для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!theme.testsLoading && tests.length != 0">
                          <v-list>
                            <v-subheader>Тесты</v-subheader>
                            <v-list-item
                                v-for="test in getFilteredThemeTests(lastOpenedTheme)"
                                :key="test.name"
                            >
                              <v-list-item-action>
                                <v-checkbox
                                    v-model="test.selectedToAdd"></v-checkbox>
                              </v-list-item-action>
                              <v-list-item-content>
                                <div class="mt-2 mb-2">
                                  <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                    <v-badge x-small color="grey darken-1" content="название">
                                      {{ test.name }}
                                    </v-badge>
                                  </span>
                                </div>
                              </v-list-item-content>
                            </v-list-item>
                          </v-list>
                          <v-row class="mt-4">
                            <v-btn block :loading="themeTestsAdding"
                                   :disabled="themeTestsAdding"
                                   @click="SendThemeTestsAddRequest(lastOpenedTheme)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="theme.testsLoading" class="pt-6 pb-6 h-50">
                  <v-row justify="center" class="text-button text-uppercase"><span
                      style="font-size: 15px;">Загрузка тестов</span>
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
                <v-container v-if="!theme.testsLoading && theme.tests.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Тесты не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!theme.testsLoading && theme.tests.length != 0">
                  <v-list-item
                      v-for="test in theme.tests"
                      :key="test.name"
                  >
                    <v-list-item-content>
                      <div class="mt-2 mb-2">
                        <span
                            style="font-family: Roboto, sans-serif; font-size: 16px;">
                          <v-badge x-small color="grey darken-1"
                                   content="название">
                            {{ test.name }}
                          </v-badge>
                        </span>
                      </div>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon :loading="test.deleting"
                             @click="SendThemeTestDeleteRequest(theme, test)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Компетенции
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="theme.themeCompetencesDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openThemeCompetencesDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="theme.competencesLoading"
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
                            v-if="!theme.competencesLoading && (competences.length == 0 || getFilteredThemeCompetences(lastOpenedTheme).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Компетенции для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!theme.competencesLoading && competences.length != 0">
                          <v-list>
                            <v-subheader>Компетенции</v-subheader>
                            <v-list-item
                                v-for="competence in getFilteredThemeCompetences(lastOpenedTheme)"
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
                            <v-btn block :loading="themeCompetencesAdding"
                                   :disabled="themeCompetencesAdding"
                                   @click="SendThemeCompetencesAddRequest(lastOpenedTheme)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="theme.competencesLoading" class="pt-6 pb-6 h-50">
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
                <v-container v-if="!theme.competencesLoading && theme.competences.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Компетенции не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!theme.competencesLoading && theme.competences.length != 0">
                  <v-list-item
                      v-for="competence in theme.competences"
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
                             @click="SendThemeCompetenceDeleteRequest(theme, competence)">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-sheet>
            </v-window-item>
            <v-window-item>
              <v-sheet rounded class="mr-4 ml-4 mb-4"
                       style="border-left: 1px solid #E0E0E0; border-right: 1px solid #E0E0E0; border-bottom: 1px solid #E0E0E0;">
                <v-toolbar flat>
                  <v-chip small color="orange" text-color="white" class="text-button">
                    Понятия
                  </v-chip>
                  <v-spacer></v-spacer>
                  <v-dialog :retain-focus="false" v-model="theme.themeConceptsDialogOpened"
                            max-width="900">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                             @click="openThemeConceptsDialog">
                        <v-icon color="green">mdi-plus</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card>
                        <v-container fluid v-if="theme.conceptsLoading"
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
                        <v-container
                            v-if="!theme.conceptsLoading && (concepts.length == 0 || getFilteredThemeConcepts(lastOpenedTheme).length == 0)"
                            class="pt-16 pb-16">
                          <v-row justify="center"
                                 class="text-button text-uppercase">
                            <span style="font-size: 18px;">Понятия для добавления не найдены</span>
                          </v-row>
                        </v-container>
                        <v-container v-else-if="!theme.conceptsLoading && concepts.length != 0">
                          <v-list>
                            <v-subheader>Понятия</v-subheader>
                            <v-list-item
                                v-for="concept in getFilteredThemeConcepts(lastOpenedTheme)"
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
                            <v-btn block :loading="themeConceptsAdding"
                                   :disabled="themeConceptsAdding"
                                   @click="SendThemeConceptsAddRequest(lastOpenedTheme)">
                              Save
                            </v-btn>
                          </v-row>
                        </v-container>
                      </v-card>
                    </template>
                  </v-dialog>
                </v-toolbar>
                <v-divider></v-divider>
                <v-container fluid v-if="theme.conceptsLoading" class="pt-6 pb-6 h-50">
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
                <v-container v-if="!theme.conceptsLoading && theme.concepts.length == 0"
                             class="pt-16 pb-16">
                  <v-row justify="center" class="text-button text-uppercase">
                    <span style="font-size: 15px;">Понятия не найдены</span>
                  </v-row>
                </v-container>
                <v-list v-if="!theme.conceptsLoading && theme.concepts.length != 0">
                  <v-list-item
                      v-for="concept in theme.concepts"
                      :key="concept.name"
                  >
                    <v-list-item-content>
                      <div class="mt-2 mb-2">
                        <span
                            style="font-family: Roboto, sans-serif; font-size: 16px;">
                          <v-badge x-small color="grey darken-1"
                                   content="название">
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
                             @click="SendThemeConceptDeleteRequest(theme, concept)">
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
  name: "v-themes",
  props: ['siteUrl'],
  data: function () {
    return {
      themes: [],
      themesLoading: false,

      createThemeState: {
        name: ''
      },
      createThemeDialogOpened: false,
      themeCreatingRequest: false,

      updateThemeDialog: false,
      updateThemeState: {
        name: ''
      },
      updatedTheme: {},
      themeUpdatingRequest: false,

      lastOpenedTheme: {},

      currentWindow: 0,

      theories: [],
      theoriesLoading: false,
      themeTheoriesAdding: false,

      tests: [],
      testsLoading: false,
      themeTestsAdding: false,

      competences: [],
      competencesLoading: false,
      themeCompetencesAdding: false,

      concepts: [],
      conceptsLoading: false,
      themeConceptsAdding: false,
    };
  },
  methods: {
    openThemeCreateDialog: function () {
      this.createThemeDialogOpened = true;
      this.createThemeState.name = '';
    },
    SendThemeCreateRequest: function (state) {
      let body = {
        newTheme: state
      };
      this.themeCreatingRequest = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theme`;
      axios.post(route, body)
          .then((response) => {
            let createdTheme = JSON.parse(response.data).createdTheme;
            this.themes.push({
              id: createdTheme.id,
              name: createdTheme.name,
              theories: [],
              theoriesLoading: false,
              theoriesLoaded: false,
              themeTheoriesDialogOpened: false,
              tests: [],
              testsLoading: false,
              testsLoaded: false,
              themeTestsDialogOpened: false,
              competences: [],
              competencesLoading: false,
              competencesLoaded: false,
              themeCompetencesDialogOpened: false,
              concepts: [],
              conceptsLoading: false,
              conceptsLoaded: false,
              themeConceptsDialogOpened: false,
              active: false,
              deleting: false,
            });
            this.themeCreatingRequest = false;
            this.createThemeDialogOpened = false;
            console.log('creating theme success');
            console.log(response);
          })
          .catch((error) => {
            this.themeCreatingRequest = false;
            console.log('creating theme failed');
            console.log(error.response.data.message);
          });
    },

    SendThemeDeleteRequest: function (theme, event) {
      event.stopPropagation();
      theme.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}`)
          .then((response) => {
            let deletedTheme = JSON.parse(response.data).deletedTheme;
            this.themes = this.themes.filter(currentTheme => currentTheme.id != deletedTheme.id);
            theme.deleting = false;
            console.log('theme deleting success');
            console.log(response);
          })
          .catch((error) => {
            theme.deleting = false;
            console.log('theme deleting failed');
            console.log(error.response.data.message);
          });
    },

    openThemeUpdateDialog: function (theme, event) {
      event.stopPropagation();
      this.updateThemeState.name = theme.name;
      this.updatedTheme = theme;
    },
    SendThemeUpdateRequest: function (theme, state) {

      let body = {
        newTheme: state
      };
      this.themeUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}`, body)
          .then((response) => {
            let updatedTheme = JSON.parse(response.data).updatedTheme;
            theme.id = updatedTheme.id;
            theme.name = updatedTheme.name;
            this.themeUpdatingRequest = false;
            this.updateThemeDialog = false;
            console.log('update theme success');
            console.log(response);
          })
          .catch((error) => {
            this.themeUpdatingRequest = false;
            console.log('update theme failed');
            console.log(error.response.data.message);
          });
    },

    themeEntitiesOpen: function (theme) {
      this.lastOpenedTheme = theme;
      this.currentWindow = 0;
      if (!theme.theoriesLoaded) {
        theme.theoriesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/theories`;
        axios.get(route)
            .then((response) => {
              theme.theoriesLoading = false;
              theme.theoriesLoaded = true;
              theme.theories = JSON.parse(response.data).theories.map(theory => {
                theory.deleting = false;
                return theory;
              });
              console.log('theories loading success');
              console.log(response);
            })
            .catch((error) => {
              theme.theoriesLoading = false;
              console.log('theories loading failed');
              console.log(error.response.data.message);
            });
      }
      if (!theme.testsLoaded) {
        theme.testsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/tests`;
        axios.get(route)
            .then((response) => {
              theme.testsLoading = false;
              theme.testsLoaded = true;
              theme.tests = JSON.parse(response.data).tests.map(test => {
                test.deleting = false;
                return test;
              });
              console.log('tests loading success');
              console.log(response);
            })
            .catch((error) => {
              theme.testsLoading = false;
              console.log('tests loading failed');
              console.log(error.response.data.message);
            });
      }
      if (!theme.competencesLoaded) {
        theme.competencesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/competences`;
        axios.get(route)
            .then((response) => {
              theme.competencesLoading = false;
              theme.competencesLoaded = true;
              theme.competences = JSON.parse(response.data).competences.map(competence => {
                competence.deleting = false;
                return competence;
              });
              console.log('competences loading success');
              console.log(response);
            })
            .catch((error) => {
              theme.competencesLoading = false;
              console.log('competences loading failed');
              console.log(error.response.data.message);
            });
      }
      if (!theme.conceptsLoaded) {
        theme.conceptsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/concepts`;
        axios.get(route)
            .then((response) => {
              theme.conceptsLoading = false;
              theme.conceptsLoaded = true;
              theme.concepts = JSON.parse(response.data).concepts.map(concept => {
                concept.deleting = false;
                return concept;
              });
              console.log('concepts loading success');
              console.log(response);
            })
            .catch((error) => {
              theme.conceptsLoading = false;
              console.log('concepts loading failed');
              console.log(error.response.data.message);
            });
      }
    },

    sanitizeTheory: function (theory) {
      return {id: theory.id, name: theory.name};
    },
    openThemeTheoriesDialog: function (theme) {
      theme.themeTheoriesDialogOpened = true;
      if (this.theories.length == 0) {
        this.theoriesLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/theories`;
        axios.get(route)
            .then((response) => {
              this.theoriesLoading = false;
              this.theories = JSON.parse(response.data).theories;
              console.log('theories loading success');
              console.log(response);
            })
            .catch((error) => {
              this.theoriesLoading = false;
              console.log('theories loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredThemeTheories: function (theme) {
      return this.theories
          .filter(theory => !theme.theories.map(currentTheory => currentTheory.name).includes(theory.name))
          .map(theory => {
            theory.selectedToAdd = false;
            return theory;
          });
    },
    SendThemeTheoriesAddRequest: function (theme) {
      this.themeTheoriesAdding = true;
      let sanitizedTheories = this.theories.filter(theory => theory.selectedToAdd).map(theory => this.sanitizeTheory(theory));
      this.theories.forEach(theory => theory.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/theories`;
      let body = {
        theories: sanitizedTheories
      };
      axios.post(route, body)
          .then((response) => {
            let addedTheories = JSON.parse(response.data).addedTheories.map(theory => {
              theory.deleting = false;
              return theory;
            });
            theme.theories.push(...addedTheories);
            this.themeTheoriesAdding = false;
            theme.themeTheoriesDialogOpened = false;
            console.log('theme theories adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.themeTheoriesAdding = false;
            console.log('theme theories adding failed');
            console.log(error.response.data.message);
          })
    },
    SendThemeTheoryDeleteRequest: function (theme, theory) {
      theory.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/theory/${theory.id}`;
      axios.delete(route)
          .then((response) => {
            theory.deleting = false;
            let deletedTheoryId = JSON.parse(response.data).deletedTheoryId;
            theme.theories = theme.theories.filter(currentTheory => currentTheory.id !== deletedTheoryId);
            console.log('theme theory delete success');
            console.log(response);
          })
          .catch((error) => {
            theory.deleting = false;
            console.log('theme theory delete failed');
            console.log(error.response.data.message);
          });
    },

    sanitizeTest: function (test) {
      return {id: test.id, name: test.name};
    },
    openThemeTestsDialog: function (theme) {
      theme.themeTestsDialogOpened = true;
      if (this.tests.length == 0) {
        this.testsLoading = true;
        let route = `${this.siteUrl}/wp-json/lms/v1/tests`;
        axios.get(route)
            .then((response) => {
              this.testsLoading = false;
              this.tests = JSON.parse(response.data).tests;
              console.log('tests loading success');
              console.log(response);
            })
            .catch((error) => {
              this.testsLoading = false;
              console.log('tests loading failed');
              console.log(error.response.data.message);
            })
      }
    },
    getFilteredThemeTests: function (theme) {
      return this.tests
          .filter(test => !theme.tests.map(currentTest => currentTest.name).includes(test.name))
          .map(test => {
            test.selectedToAdd = false;
            return test;
          });
    },
    SendThemeTestsAddRequest: function (theme) {
      this.themeTestsAdding = true;
      let sanitizedTests = this.tests.filter(test => test.selectedToAdd).map(test => this.sanitizeTest(test));
      this.tests.forEach(test => test.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/tests`;
      let body = {
        tests: sanitizedTests
      };
      axios.post(route, body)
          .then((response) => {
            let addedTests = JSON.parse(response.data).addedTests.map(test => {
              test.deleting = false;
              return test;
            });
            theme.tests.push(...addedTests);
            this.themeTestsAdding = false;
            theme.themeTestsDialogOpened = false;
            console.log('theme tests adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.themeTestsAdding = false;
            console.log('theme tests adding failed');
            console.log(error.response.data.message);
          })
    },
    SendThemeTestDeleteRequest: function (theme, test) {
      test.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/test/${test.id}`;
      axios.delete(route)
          .then((response) => {
            test.deleting = false;
            let deletedTestId = JSON.parse(response.data).deletedTestId;
            theme.tests = theme.tests.filter(currentTest => currentTest.id !== deletedTestId);
            console.log('theme test delete success');
            console.log(response);
          })
          .catch((error) => {
            test.deleting = false;
            console.log('theme test delete failed');
            console.log(error.response.data.message);
          });
    },

    sanitizeCompetence: function (competence) {
      return {id: competence.id, name: competence.name};
    },
    openThemeCompetencesDialog: function (theme) {
      theme.themeCompetencesDialogOpened = true;
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
    getFilteredThemeCompetences: function (theme) {
      return this.competences
          .filter(competence => !theme.competences.map(currentCompetence => currentCompetence.name).includes(competence.name))
          .map(competence => {
            competence.selectedToAdd = false;
            return competence;
          });
    },
    SendThemeCompetencesAddRequest: function (theme) {
      this.themeCompetencesAdding = true;
      let sanitizedCompetences = this.competences.filter(competence => competence.selectedToAdd).map(competence => this.sanitizeCompetence(competence));
      this.competences.forEach(competence => competence.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/competences`;
      let body = {
        competences: sanitizedCompetences
      };
      axios.post(route, body)
          .then((response) => {
            let addedCompetences = JSON.parse(response.data).addedCompetences.map(competence => {
              competence.deleting = false;
              return competence;
            });
            theme.competences.push(...addedCompetences);
            this.themeCompetencesAdding = false;
            theme.themeCompetencesDialogOpened = false;
            console.log('theme competences adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.themeCompetencesAdding = false;
            console.log('theme competences adding failed');
            console.log(error.response.data.message);
          })
    },
    SendThemeCompetenceDeleteRequest: function (theme, competence) {
      competence.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/competence/${competence.id}`;
      axios.delete(route)
          .then((response) => {
            competence.deleting = false;
            let deletedCompetenceId = JSON.parse(response.data).deletedCompetenceId;
            theme.competences = theme.competences.filter(currentCompetence => currentCompetence.id !== deletedCompetenceId);
            console.log('theme competence delete success');
            console.log(response);
          })
          .catch((error) => {
            competence.deleting = false;
            console.log('theme competence delete failed');
            console.log(error.response.data.message);
          });
    },

    sanitizeConcept: function (concept) {
      return {id: concept.id, name: concept.name, weight: concept.weight};
    },
    openThemeConceptsDialog: function (theme) {
      theme.themeConceptsDialogOpened = true;
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
    getFilteredThemeConcepts: function (theme) {
      return this.concepts
          .filter(concept => !theme.concepts.map(currentConcept => currentConcept.name).includes(concept.name))
          .map(concept => {
            concept.selectedToAdd = false;
            return concept;
          });
    },
    SendThemeConceptsAddRequest: function (theme) {
      this.themeConceptsAdding = true;
      let sanitizedConcepts = this.concepts.filter(concept => concept.selectedToAdd).map(concept => this.sanitizeConcept(concept));
      this.concepts.forEach(concept => concept.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/concepts`;
      let body = {
        concepts: sanitizedConcepts
      };
      axios.post(route, body)
          .then((response) => {
            let addedConcepts = JSON.parse(response.data).addedConcepts.map(concept => {
              concept.deleting = false;
              return concept;
            });
            theme.concepts.push(...addedConcepts);
            this.themeConceptsAdding = false;
            theme.themeConceptsDialogOpened = false;
            console.log('theme concepts adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.themeConceptsAdding = false;
            console.log('theme concepts adding failed');
            console.log(error.response.data.message);
          })
    },
    SendThemeConceptDeleteRequest: function (theme, concept) {
      concept.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/theme/${theme.id}/concept/${concept.id}`;
      axios.delete(route)
          .then((response) => {
            concept.deleting = false;
            let deletedConceptId = JSON.parse(response.data).deletedConceptId;
            theme.concepts = theme.concepts.filter(currentConcept => currentConcept.id !== deletedConceptId);
            console.log('theme concept delete success');
            console.log(response);
          })
          .catch((error) => {
            concept.deleting = false;
            console.log('theme concept delete failed');
            console.log(error.response.data.message);
          });
    },
  },
  mounted() {
    this.themesLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/themes`;
    axios.get(route)
        .then((response) => {
          this.themesLoading = false;
          this.themes = JSON.parse(response.data).themes.map(theme => {
            theme.active = false;
            theme.deleting = false;
            theme.theories = [];
            theme.theoriesLoading = false;
            theme.theoriesLoaded = false;
            theme.themeTheoriesDialogOpened = false;
            theme.tests = [];
            theme.testsLoading = false;
            theme.testsLoaded = false;
            theme.themeTestsDialogOpened = false;
            theme.competences = [];
            theme.competencesLoading = false;
            theme.competencesLoaded = false;
            theme.themeCompetencesDialogOpened = false;
            theme.concepts = [];
            theme.conceptsLoading = false;
            theme.conceptsLoaded = false;
            theme.themeConceptsDialogOpened = false;
            return theme;
          });
          console.log('themes loading success');
          console.log(response);
        })
        .catch((error) => {
          this.themesLoading = false;
          console.log('themes loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>