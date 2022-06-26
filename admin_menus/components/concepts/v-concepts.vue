<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Понятия</v-chip>
      <v-spacer></v-spacer>
      <v-dialog :retain-focus="false" v-model="createConceptDialogOpened" max-width="700">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on" class="mb-3"
                 @click="openConceptCreateDialog">
            <v-icon color="green">mdi-plus</v-icon>
          </v-btn>
        </template>
        <template v-slot:default>
          <v-card class="d-flex flex-column justify-content-center">
            <v-list>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field autofocus
                                v-model="createConceptState.name"
                                label="название">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-content>
                  <v-text-field
                      v-model="createConceptState.weight"
                      label="сложность понятия">
                  </v-text-field>
                </v-list-item-content>
              </v-list-item>
            </v-list>
            <v-btn :loading="conceptCreatingRequest" :disabled="conceptCreatingRequest"
                   justify-self="center"
                   @click="SendConceptCreateRequest(createConceptState)">
              Save
            </v-btn>
          </v-card>
        </template>
      </v-dialog>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="conceptsLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка понятий</span>
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
    <v-container v-if="!conceptsLoading && concepts.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Понятия не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!conceptsLoading && concepts.length != 0">
      <v-list-item
          v-for="concept in concepts"
          :key="concept.name"
          v-model="concept.activate"
      >
        <v-list-item-content>
          <div class="mt-2 mb-2">
                                            <span style="font-family: Roboto, sans-serif; font-size: 16px;">
                                                <v-badge x-small color="grey darken-1" content="название">
                                                        {{ concept.name }}
                                                </v-badge>
                                            </span>
            <span class="ml-16"
                  style="font-family: Roboto, sans-serif; font-size: 16px;">
                                                <v-badge x-small color="grey darken-1" content="вес">
                                                        {{ concept.weight }}
                                                </v-badge>
                                            </span>
          </div>
        </v-list-item-content>
        <v-list-item-action>
          <v-dialog :retain-focus="false" v-model="updateConceptDialog"
                    max-width="700">
            <template v-slot:activator="{ on, attrs }">
              <v-btn v-bind="attrs" v-on="on" icon
                     @click="(event) => openConceptUpdateDialog(concept, event)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <template v-slot:default>
              <v-card class="d-flex flex-column justify-content-center">
                <v-list>
                  <v-list-item>
                    <v-list-item-content>
                      <v-text-field autofocus
                                    v-model="updateConceptState.name"
                                    label="название">
                      </v-text-field>
                    </v-list-item-content>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-content>
                      <v-text-field
                          v-model="updateConceptState.weight"
                          label="сложность понятия">
                      </v-text-field>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>
                <v-btn :loading="conceptUpdatingRequest"
                       :disabled="conceptUpdatingRequest" justify-self="center"
                       @click="SendConceptUpdateRequest(updatedConcept, updateConceptState)">
                  Save
                </v-btn>
              </v-card>
            </template>
          </v-dialog>
        </v-list-item-action>
        <v-list-item-action>
          <v-btn icon :loading="concept.deleting"
                 @click="(event) => SendConceptDeleteRequest(concept, event)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </v-list-item-action>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
module.exports = {
  name: "v-concepts",
  props: ['siteUrl'],
  data: function () {
    return {
      concepts: [],
      conceptsLoading: false,

      createConceptState: {
        name: '',
        weight: 0.0
      },
      createConceptDialogOpened: false,
      conceptCreatingRequest: false,

      updateConceptDialog: false,
      updateConceptState: {
        name: '',
        weight: 0.0,
      },
      updatedConcept: {},
      conceptUpdatingRequest: false,
    };
  },
  methods: {
    openConceptCreateDialog: function () {
      this.createConceptDialogOpened = true;
      this.createConceptState.name = '';
      this.createConceptState.weight = 0.0;
    },
    SendConceptCreateRequest: function (conceptState) {
      this.conceptCreatingRequest = true;
      let body = {
        newConcept: conceptState
      };
      console.log(body);
      axios.post(`${this.siteUrl}/wp-json/lms/v1/concept`, body)
          .then((response) => {
            let createdConcept = JSON.parse(response.data).createdConcept;
            this.concepts.push({
              id: createdConcept.id,
              name: createdConcept.name,
              weight: createdConcept.weight,
              active: false,
              deleting: false,
            });
            this.conceptCreatingRequest = false;
            this.createConceptDialogOpened = false;
            console.log('creating concept success');
            console.log(response);
          })
          .catch((error) => {
            this.conceptCreatingRequest = false;
            console.log('creating concept failed');
            console.log(error.response.data.message);
          });
    },

    openConceptUpdateDialog: function (concept, event) {
      event.stopPropagation();
      this.updateConceptState.name = concept.name;
      this.updateConceptState.weight = concept.weight;
      this.updatedConcept = concept;
    },
    SendConceptUpdateRequest: function (concept, updateConceptState) {
      let body = {
        newConcept: updateConceptState
      };
      this.conceptUpdatingRequest = true;
      axios.put(`${this.siteUrl}/wp-json/lms/v1/concept/${concept.id}`, body)
          .then((response) => {
            let updatedConcept = JSON.parse(response.data).updatedConcept;
            concept.id = updatedConcept.id;
            concept.name = updatedConcept.name;
            concept.weight = updatedConcept.weight;
            this.conceptUpdatingRequest = false;
            this.updateConceptDialog = false;
            console.log('update concept success');
            console.log(response);
          })
          .catch((error) => {
            this.conceptUpdatingRequest = false;
            console.log('update concept failed');
            console.log(error.response.data.message);
          });
    },

    SendConceptDeleteRequest: function (concept, event) {
      event.stopPropagation();
      concept.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/concept/${concept.id}`)
          .then((response) => {
            let deletedConcept = JSON.parse(response.data).deletedConcept;
            console.log(concept);
            console.log(deletedConcept);
            this.concepts = this.concepts.filter(currentConcept => currentConcept.id != deletedConcept.id);
            concept.deleting = false;
            console.log('concept deleting success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            concept.deleting = false;
            console.log('concept deleting failed');
            console.log(error.response.data.message);
          });
    },
  },
  mounted() {
    this.conceptsLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/concepts`;
    axios.get(route)
        .then((response) => {
          this.conceptsLoading = false;
          this.concepts = JSON.parse(response.data).concepts.map(concept => {
            concept.active = false;
            concept.deleting = false;
            return concept;
          });
          console.log('concepts loading success');
          console.log(response);
        })
        .catch((error) => {
          this.conceptsLoading = false;
          console.log('concepts loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>