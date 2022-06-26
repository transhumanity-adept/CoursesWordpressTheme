<template>
  <v-card class="pt-3 pb-3" style="min-width: 100%">
    <v-toolbar flat>
      <v-chip small color="primary" class="text-button">Варианты ответа</v-chip>
      <v-spacer></v-spacer>
      <v-btn icon class="mb-3"
             @click="SendOptionCreateRequest"
             :loading="optionCreatingRequest"
             :disabled="optionCreatingRequest"
      >
        <v-icon color="green">mdi-plus</v-icon>
      </v-btn>
    </v-toolbar>
    <v-divider></v-divider>
    <v-container fluid v-if="optionsLoading" class="pt-6 pb-6 h-50">
      <v-row justify="center" class="text-button text-uppercase"><span
          style="font-size: 17px;">Загрузка вариантов ответа</span>
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
    <v-container v-if="!optionsLoading && options.length == 0" class="pt-16 pb-16">
      <v-row justify="center" class="text-button text-uppercase">
        <span style="font-size: 18px;">Варианты ответа не найдены</span>
      </v-row>
    </v-container>
    <v-list v-if="!optionsLoading && options.length != 0">
      <v-list-item
          v-for="option in options"
          :key="option.name"
          v-model="option.activate"
      >
        <v-list-item-content>
          <div
              v-for="block in JSON.parse(option.value).blocks"
              :key="block.id"
              class="mt-2 mb-2"
              style="padding-right: 100px !important;"
          >
            <pre style="font-size: 14px;" v-if="block.type == 'code'" class="line-numbers language-csharp"><code :id="block.id+'_view'"></code></pre>
            <ul style="font-size: 17px; line-height: 27px;" v-else-if="block.type == 'list'">
              <li v-for="item in block.data.items" v-html="item"></li>
            </ul>
            <span v-else-if="block.type == 'header'" v-html="`<h${block.data.level}>${block.data.text}</h${block.data.level}>`"></span>
            <p style="font-size: 17px; line-height: 27px;" v-html="block.data.text" v-else></p>
          </div>
        </v-list-item-content>
        <v-list-item-action>
          <v-dialog
              @click:outside="destroyEditor(option)"
              :retain-focus="false"
              v-model="option.updateOptionValueDialog"
              fullscreen
              hide-overlay
              transition="dialog-bottom-transition"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn class="mr-4" v-bind="attrs" v-on="on" icon
                     @click="(event) => openOptionUpdateValueDialog(option, event)">
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
                          Ответ
                        </span>
                  </v-toolbar-title>
                  <v-spacer></v-spacer>
                  <v-toolbar-items>
                    <v-btn
                        :loading="option.optionValueUpdating"
                        :disabled="option.optionValueUpdating"
                        @click="SendUpdateOptionValueRequest(option)"
                        color="primary"
                        text
                    >
                      <span class="white--text">Save</span>
                    </v-btn>
                    <v-btn
                        icon
                        @click="closeUpdateOptionValueDialog(option)"
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
                      <div  :id="option.id"></div>
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
                          v-for="block in option.editorData.blocks"
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
          <v-btn icon :loading="option.deleting"
                 @click="(event) => SendOptionDeleteRequest(option, event)">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </v-list-item-action>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
module.exports = {
  name: "v-options",
  props: ['siteUrl'],
  data: function () {
    return {
      options: [],
      optionsLoading: false,

      optionCreatingRequest: false,

      lastUpdateValueOption: {},
    };
  },
  methods: {
    SendOptionCreateRequest: function () {
      this.optionCreatingRequest = true;
      axios.post(`${this.siteUrl}/wp-json/lms/v1/option`)
          .then((response) => {
            let createdOption = JSON.parse(response.data).createdOption;
            this.options.push({
              id: createdOption.id,
              value: createdOption.value,
              active: false,
              deleting: false,
              updateOptionValueDialog: false,
              optionValueUpdating: false,
              editor: {},
              editorData: JSON.parse(createdOption.value),
            });
            this.optionCreatingRequest = false;
            console.log('creating option success');
            console.log(response);
          })
          .catch((error) => {
            this.optionCreatingRequest = false;
            console.log('creating option failed');
            console.log(error.response.data.message);
          });
    },

    SendOptionDeleteRequest: function (option, event) {
      event.stopPropagation();
      option.deleting = true;
      axios.delete(`${this.siteUrl}/wp-json/lms/v1/option/${option.id}`)
          .then((response) => {
            let deletedOption = JSON.parse(response.data).deletedOption;
            console.log(option);
            console.log(deletedOption);
            this.options = this.options.filter(currentOption => currentOption.id != deletedOption.id);
            option.deleting = false;
            console.log('option deleting success');
            console.log(response);
          })
          .catch((error) => {
            console.log(error);
            option.deleting = false;
            console.log('option deleting failed');
            console.log(error.response.data.message);
          });
    },

    async openOptionUpdateValueDialog(option, event) {
      event.stopPropagation();
      this.lastUpdateValueOption = option;
      option.updateOptionValueDialog = true;
      await this.$nextTick();
      const editor = new EditorJS({
        holder: option.id,
        data: option.editorData,
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
      option.editor = editor;
      let filteredBlocks = option.editorData.blocks.filter(block => block.type == 'code');
      for (const filteredBlock of filteredBlocks) {
        document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
      }
      Prism.highlightAll();
    },
    editorChanged: function() {
      this.lastUpdateValueOption.editor.save()
          .then((outputData) => {
            this.lastUpdateValueOption.editorData = outputData;
            this.$nextTick()
                .then(() => {
                  let filteredBlocks = this.lastUpdateValueOption.editorData.blocks.filter(block => block.type == 'code');
                  for (const filteredBlock of filteredBlocks) {
                    document.getElementById(filteredBlock.id).innerHTML = filteredBlock.data.code;
                  }
                  Prism.highlightAll();
                });
          });
    },
    destroyEditor: function (option){
      option.editor.destroy();
    },
    closeUpdateOptionValueDialog: function(option) {
      this.destroyEditor(option);
      option.updateOptionValueDialog = false;
    },
    SendUpdateOptionValueRequest: function (option) {
      option.optionValueUpdating = true;
      option.editor.save()
          .then(outputData => {
            let body = {
              newValue: JSON.stringify(outputData)
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
            let route = `${this.siteUrl}/wp-json/lms/v1/option/${option.id}/value`;
            axios.put(route, body, config)
                .then((response) => {
                  let updatedOption = JSON.parse(response.data).updatedOption;
                  option.id = updatedOption.id;
                  option.name = updatedOption.name;
                  option.value = updatedOption.value;
                  option.optionValueUpdating = false;
                  option.updateOptionValueDialog = false;
                  option.editor.blocks.clear();
                  option.editor.destroy();
                  console.log('option update success');
                  console.log(response);
                })
                .catch((error) => {
                  option.optionValueUpdating = false;
                  console.log('option update failed');
                  console.log(error.response.data.message);
                });
          })

    },
  },
  computed: {
    values() {
      return this.options.map(option => option.value);
    }
  },
  watch : {
    values(newValues) {
      this.$nextTick()
          .then(() => {
            newValues.forEach(value => {
              let filteredBlocks = JSON.parse(value).blocks.filter(block => block.type == 'code');
              for (const filteredBlock of filteredBlocks) {
                document.getElementById(filteredBlock.id+'_view').innerHTML = filteredBlock.data.code;
              }
            });
            Prism.highlightAll();
          });
    }
  },
  mounted() {
    this.optionsLoading = true;
    let route = `${this.siteUrl}/wp-json/lms/v1/options`;
    axios.get(route)
        .then((response) => {
          this.optionsLoading = false;
          this.options = JSON.parse(response.data).options.map(option => {
            option.active = false;
            option.deleting = false;
            option.updateOptionValueDialog = false;
            option.optionValueUpdating = false;
            option.editor = {};
            option.editorData = JSON.parse(option.value);
            return option;
          });
          console.log('options loading success');
          console.log(response);
        })
        .catch((error) => {
          this.optionsLoading = false;
          console.log('options loading failed');
          console.log(error.response.data.message);
        })
  }
}
</script>

<style scoped>

</style>