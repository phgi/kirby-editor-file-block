<template>
  <div class="k-editor-file-block-wrapper">
      <template v-if="attrs.src">
        <div class="k-editor-file-block-inner">
          <div
            :style="style"
            ref="element"
            class="k-editor-file-block-file"
            @keydown.delete="$emit('remove')"
            @keydown.enter="$emit('append')"
            @keydown.up="$emit('prev')"
            @keydown.down="$emit('next')"
            tabindex="0"
            >
              <p class="k-editor-file-block-filename" ref="element">{{ attrs.id }}</p>
            </div>
                <k-editable
                  :content="attrs.linkText"
                  :breaks="true"
                  :placeholder="$t('editor.blocks.file.linkTextPlaceholder') + '…'"
                  class="k-editor-file-block-linktext"
                  @prev="focus"
                  @shiftTab="focus"
                  @tab="$emit('next', $event)"
                  @next="$emit('next', $event)"
                  @split="$emit('append')"
                  @enter="$emit('append')"
                  @keydown.delete="$emit()"
                  @input="linkText"
                />
        </div>
      </template>

      <template v-else>
        <div>
          <k-dropzone
            ref="element"
            class="k-editor-file-block-placeholder"
            tabindex="0"
            @keydown.native.delete="$emit('remove')"
            @keydown.native.enter="$emit('append')"
            @keydown.native.up.prevent="$emit('prev')"
            @keydown.native.down.prevent="$emit('next')"
            @drop="onDrop"
          >
            <k-button icon="upload" @click="uploadFile" @keydown.enter.native.stop>{{ $t('editor.blocks.file.upload') }}</k-button>
            {{ $t('editor.blocks.file.or') }}
            <k-button icon="file" @click="selectFile" @keydown.enter.native.stop>{{ $t('editor.blocks.file.select') }}</k-button>
          </k-dropzone>
        </div>
      </template>

    <k-files-dialog ref="fileDialog" @submit="insertFile($event)" />
    <k-upload ref="fileUpload" @success="insertUpload" />

    <k-dialog ref="settings" @submit="saveSettings" size="medium">
      <k-form :fields="fields" v-model="attrs" @submit="saveSettings" />
    </k-dialog>

  </div>
</template>

<script>
export default {
  label: "File",
  icon: "document",
  props: {
    attrs: {
      type: Object,
      default() {
        return {}
      }
    },
    endpoints: Object,
    spellcheck: Boolean
  },
  computed: {
    fields() {
      return {
        target: {
          label: this.$t('editor.blocks.file.target'),
          type: "radio",
          default: "_self",
          options: [
            {
              text: this.$t('editor.blocks.file.sameWindow'),
              value: "_self",
              default: true
            },
            {
              text: this.$t('editor.blocks.file.newWindow'),
              value: "_blank"
            },
          ]
        }
      }
    }
  },
  methods: {
    linkText(html) {
      this.input({
        linkText: html
      });
    },
    edit() {
      if (this.attrs.guid) {
        this.$router.push(this.attrs.guid);
      }
    },
    input(data) {
      this.$emit("input", {
        attrs: {
          ...this.attrs,
          ...data
        }
      });
    },
    fetchFile(link) {
      this.$api.get(link).then(response => {
        this.input({
          guid: response.link,
          src: response.url,
          id: response.id
        });
      });
    },
    focus() {
      if (this.attrs.src) {
        this.$refs.element.focus();
      } else {
        this.$refs.element.$el.focus();
      }
    },
    insertFile(files) {
      const file = files[0];
      this.fetchFile(file.link);
    },
    insertUpload(files, response) {
      this.fetchFile(response[0].link);
      this.$events.$emit("file.create");
      this.$events.$emit("model.update");
      this.$store.dispatch("notification/success", ":)");
    },
    menu() {
      if (this.attrs.src) {
        return [
       {
            icon: "open",
            label: this.$t("editor.blocks.image.open.browser"),
            click: this.open
          },
          {
            icon: "edit",
            label: this.$t("editor.blocks.image.open.panel"),
            click: this.edit,
            disabled: !this.attrs.guid
          },
          {
            icon: "cog",
            label: this.$t("editor.blocks.file.settings"),
            click: this.$refs.settings.open
          }
        ];
      } else {
        return [];
      }
    },
    open() {
      window.open(this.attrs.src);
    },
    onDrop(files) {
      this.$refs.fileUpload.drop(files, {
        url: window.panel.api + "/" + this.endpoints.field + "/upload",
        multiple: false
      });
    },
    onLoad() {
      const image = this.$refs.image;
      if (!this.attrs.ratio && image && image.width && image.height) {
        this.input({
          ratio: image.width / image.height
        });
      }
    },
    replace() {
      this.$emit("input", {
        attrs: {}
      });
    },
    selectFile() {
      this.$refs.fileDialog.open({
        endpoint: this.endpoints.field + "/files",
        multiple: false,
        selected: [this.attrs.id]
      });
    },
    settings() {
      this.$refs.settings.open();
    },
    saveSettings() {
      this.$refs.settings.close();
      this.input(this.attrs);
    },
    uploadFile() {
      this.$refs.fileUpload.open({
        url: window.panel.api + "/" + this.endpoints.field + "/upload",
        multiple: false,
        accept: "*"
      });
    },
  }
}
</script>

<style>
.k-editor-file-block {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

.k-editor-file-block-wrapper {
  line-height: 1;
  background: var(--color-background);
  border-radius: 4px;
  color: var(--color-text-light);

}

.k-editor-file-block-inner {
  padding: .75rem;
}

.k-editor-file-block-placeholder {
  display: flex;
  line-height: 1;
  justify-content: center;
  align-items: center;
  font-style: italic;
  font-size: .875rem;
  width: 100%;
  background: var(--color-background);
  border: 1px solid transparent;
  border-radius: 4px;
}

.k-editor-file-block-placeholder:focus {
  outline: 2px solid var(--color-focus-outline);
  outline-offset: 2px;
}

.k-editor-file-block-placeholder .k-button {
  margin: 0 1.5rem;
  padding: .75rem;
}

.k-editor-file-block-file:focus {
  outline: 2px solid var(--color-focus-outline);
  outline-offset: 2px;
}

.k-editor-file-block-filename {
  font-family: var(--font-family-mono);
  color: var(--color-text);
  cursor: default;
}

.k-editor-file-block-filename * {
  vertical-align: middle;
}

.k-editor-file-block-filename .k-icon {
  display: inline-flex;
}

.k-editor-file-block-linktext {
  color: var(--color-text-light);
}

</style>
