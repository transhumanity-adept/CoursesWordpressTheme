<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Компетенции</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createCompetenceDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openCompetenceCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createCompetenceState.name"
                                label="название"
                                @keydown.enter="SendCompetenceCreateRequest(createCompetenceState)">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="competenceCreatingRequest" :disabled="competenceCreatingRequest"
                   justify-self="center"
                   @click="SendCompetenceCreateRequest(createCompetenceState)">Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="competencesLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка компетенций</span>
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
    <v-container v-if="!competencesLoading && competences.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Компетенции не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!competencesLoading && competences.length != 0">
      <v-list-group
          v-for="competence in competences"
          :key="competence.name"
          v-model="competence.active"
          no-action
          @click="competenceConceptsOpen(competence)"
      >
        <template v-slot:activator>
          <v-list-item>
            <v-list-item-content>
              <div class="mt-2 mb-2">
                <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                  <v-badge x-small color="grey darken-1" content="название">
                    {{ competence.name }}
                  </v-badge>
                </span>
              </div>
            </v-list-item-content>
            <v-list-item-action>
              <v-dialog :retain-focus="false" v-model="updateCompetenceDialog"
                        max-width="700">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn v-bind="attrs" v-on="on" icon
                         @click="(event) => openCompetenceUpdateDialog(competence, event)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <template v-slot:default>
                  <v-card class="d-flex flex-column justify-content-center">
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field autofocus
                                        v-model="updateCompetenceState.name"
                                        label="название"
                                        @keydown.enter="SendCompetenceUpdateRequest(updatedCompetence, updateCompetenceState)">
                          </v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                    <v-btn :loading="competenceUpdatingRequest"
                           :disabled="competenceUpdatingRequest"
                           justify-self="center"
                           @click="SendCompetenceUpdateRequest(updatedCompetence, updateCompetenceState)">
                      Save
                    </v-btn>
                  </v-card>
                </template>
              </v-dialog>
            </v-list-item-action>
            <v-list-item-action>
              <v-btn icon :loading="competence.deleting"
                     @click="(event) => SendCompetenceDeleteRequest(competence, event)">
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
              <v-dialog :retain-focus="false" v-model="competenceConceptsDialogOpened"
                        max-width="900">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon class="ml-3" v-bind="attrs" v-on="on"
                         @click="openCompetenceConceptsDialog">
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
                    <v-container v-if="!conceptsLoading && (concepts.length == 0 || getFilteredCompetenceConcepts(lastOpenedCompetence).length == 0)"
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
                            v-for="concept in getFilteredCompetenceConcepts(lastOpenedCompetence)"
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
                        <v-btn block :loading="competenceConceptsAdding"
                               :disabled="competenceConceptsAdding"
                               @click="SendCompetenceConceptsAddRequest(lastOpenedCompetence)">
                          Save
                        </v-btn>
                      </v-row>
                    </v-container>
                  </v-card>
                </template>
              </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-container fluid v-if="competence.conceptsLoading" class="pt-6 pb-6 h-50">
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
            <v-container v-if="!competence.conceptsLoading && competence.concepts.length == 0"
                         class="pt-16 pb-16">
              <v-row justify="center" class="text-button text-uppercase">
                <span style="font-size: 15px;">Понятия не найдены</span>
              </v-row>
            </v-container>
            <v-list v-if="!competence.conceptsLoading && competence.concepts.length != 0">
              <v-list-item
                  v-for="concept in competence.concepts"
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
                         @click="SendCompetenceConceptDeleteRequest(competence, concept)">
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
  name: "v-competences",
  props: ['siteUrl'],
  data: function () {
    return {
      concepts: [],
      conceptsLoading: false,

      competences: [],
      competencesLoading: false,
      createCompetenceState: {
        name: '',
      },
      createCompetenceDialogOpened: false,
      competenceCreatingRequest: false,
      lastOpenedCompetence: {},

      updateCompetenceDialog: false,
      updateCompetenceState: {
        name: '',
      },
      updatedCompetence: {},
      competenceUpdatingRequest: false,

      competenceConceptsDialogOpened: false,
      competenceConceptsAdding: false,
    };
  },
  methods: {
    sanitizeConcept: function (concept) {
      return {id: concept.id, name: concept.name, weight: concept.weight};
    },
    openCompetenceCreateDialog: function () {
      this.createCompetenceDialogOpened = true;
      this.createCompetenceState.name = '';
    },
    SendCompetenceCreateRequest: function (competenceState) {
      this.competenceCreatingRequest = true;
      let body = {
        newCompetence: competenceState
      };
      console.log(body);
      axios.post(`${this.siteUrl}/wp-json/lms/v1/competence`, body)
          .then((response) => {
            let createdCompetence = JSON.parse(response.data).createdCompetence;
            this.competences.push({
              id: createdCompetence.id,
              name: createdCompetence.name,
              concepts: [],
              conceptsLoading: false,
              conceptsLoaded: false,
              active: false,
              deleting: false,
            });
            this.competenceCreatingRequest = false;
            this.createCompetenceDialogOpened = false;
            console.log('creating competence success');
            console.log(response);
          })
          .catch((error) => {
            this.competenceCreatingRequest = false;
            console.log('creating competence failed');
            console.log(error.response.data.message);
          });
    },

    competenceConceptsOpen : function(competence) {
      this.lastOpenedCompetence = competence;
      if (competence.conceptsLoaded) return;
      competence.conceptsLoading = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/competence/${competence.id}/concepts`;
      axios.get(route)
          .then((response) => {
            competence.conceptsLoading = false;
            competence.conceptsLoaded = true;
            competence.concepts = JSON.parse(response.data).concepts.map(concept => {
              concept.deleting = false;
              return concept;
            });
            console.log('concepts loading success');
            console.log(response);
          })
          .catch((error) => {
            competence.conceptsLoading = false;
            console.log('concepts loading failed');
            console.log(error.response.data.message);
          });
    },

    openCompetenceUpdateDialog: function (competence, event) {
      event.stopPropagation();
      this.updateCompetenceState.name = competence.name;
      this.updatedCompetence = competence;
    },
    SendCompetenceUpdateRequest: function (competence, updateConceptState) {
      let body = {
        newCompetence: updateConceptState
      };
      this.competenceUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/competence/${competence.id}`, body)
          .then((response) => {
            let updatedCompetence = JSON.parse(response.data).updatedCompetence;
            competence.id = updatedCompetence.id;
            competence.name = updatedCompetence.name;
            this.competenceUpdatingRequest = false;
            this.updateCompetenceDialog = false;
            console.log('update competence success');
            console.log(response);
          })
          .catch((error) => {
            this.competenceUpdatingRequest = false;
            console.log('update competence failed');
            console.log(error.response.data.message);
          });
    },

    SendCompetenceDeleteRequest: function (competence, event) {
      event.stopPropagation();
      competence.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/competence/${competence.id}`)
          .then((response) => {
            let deletedCompetence = JSON.parse(response.data).deletedCompetence;
            this.competences = this.competences.filter(currentCompetence => currentCompetence.id != deletedCompetence.id);
            competence.deleting = false;
            console.log('competence deleting success');
            console.log(response);
          })
          .catch((error) => {
            competence.deleting = false;
            console.log('competence deleting failed');
            console.log(error.response.data.message);
          });
    },

    openCompetenceConceptsDialog: function () {
      this.competenceConceptsDialogOpened = true;
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
    getFilteredCompetenceConcepts: function (competence) {
      return this.concepts
          .filter(concept => !competence.concepts.map(currentConcept => currentConcept.name).includes(concept.name))
          .map(concept => {
            concept.selectedToAdd = false;
            return concept;
          });
    },
    SendCompetenceConceptsAddRequest: function (competence) {
      this.competenceConceptsAdding = true;
      let sanitizedConcepts = this.concepts.filter(concept => concept.selectedToAdd).map(concept => this.sanitizeConcept(concept));
      this.concepts.forEach(concept => concept.selectedToAdd = false);
      let route = `${this.siteUrl}/wp-json/lms/v1/competence/${competence.id}/concepts`;
      let body = {
        concepts: sanitizedConcepts
      };
      axios.post(route, body)
          .then((response) => {
            let addedConcepts = JSON.parse(response.data).addedConcepts.map(concept => {
              concept.deleting = false;
              return concept;
            });
            competence.concepts.push(...addedConcepts);
            this.competenceConceptsAdding = false;
            this.competenceConceptsDialogOpened = false;
            console.log('competence concepts adding success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            this.competenceConceptsAdding = false;
            console.log('competence concepts adding failed');
            console.log(error.response.data.message);
          })
    },
    SendCompetenceConceptDeleteRequest: function (competence, concept) {
      concept.deleting = true;
      let route = `${this.siteUrl}/wp-json/lms/v1/competence/${competence.id}/concept/${concept.id}`;
      axios.delete(route)
          .then((response) => {
            concept.deleting = false;
            let deletedConceptId = JSON.parse(response.data).deletedConceptId;
            competence.concepts = competence.concepts.filter(currentConcept => currentConcept.id !== deletedConceptId);
            console.log('competence concept delete success');
            console.log(response);
          })
          .catch((error) => {
            concept.deleting = false;
            console.log('competence concept delete failed');
            console.log(error.response.data.message);
          });
    },
  },
  mounted() {
    this.competencesLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/competences`;
    axios.get(route)
        .then((response) => {
          this.competencesLoading = false;
          this.competences = JSON.parse(response.data).competences.map(competence => {
            competence.active = false;
            competence.deleting = false;
            competence.concepts = [];
            competence.conceptsLoading = false;
            competence.conceptsLoaded = false;
            return competence;
          });
          console.log('competences loading success');
          console.log(response);
        })
        .catch((error) => {
          this.competencesLoading = false;
          console.log('competences loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>