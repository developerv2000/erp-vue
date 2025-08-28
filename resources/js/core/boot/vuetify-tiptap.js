import { createVuetifyProTipTap, VuetifyTiptap, VuetifyViewer } from "vuetify-pro-tiptap";
import { BaseKit, Bold, Italic, Underline, Strike, Color, Highlight, Heading, TextAlign, FontFamily, FontSize, SubAndSuperScript, BulletList, OrderedList, TaskList, Indent, Link, Image, Video, Table, Blockquote, HorizontalRule, Code, CodeBlock, Clear, Fullscreen, History } from 'vuetify-pro-tiptap'
import 'vuetify-pro-tiptap/style.css'
import axios from "axios";
import { Plugin, PluginKey } from 'prosemirror-state';

// Common extensions
export const extensions = [
    BaseKit,
    Bold,
    Italic,
    Underline,
    Strike,
    Code.configure({ divider: true }),
    Heading,
    TextAlign,
    FontFamily,
    FontSize,
    Color,
    Highlight.configure({ divider: true }),
    SubAndSuperScript.configure({ divider: true }),
    Clear.configure({ divider: true }),
    BulletList,
    OrderedList,
    TaskList,
    Indent.configure({ divider: true }),
    Link,
    Video,
    Table.configure({ divider: true }),
    Blockquote,
    HorizontalRule,
    CodeBlock.configure({ divider: true }),
    History.configure({ divider: true }),
    Fullscreen,
];

// Image extension factory
export const imageExtension = function (folder = 'default') {
    return Image.extend({
        addProseMirrorPlugins() {
            return [
                new Plugin({
                    key: new PluginKey('imagePaste'),
                    props: {
                        handlePaste: (view, event) => {
                            const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                            for (const item of items) {
                                if (item.type.startsWith('image/')) {
                                    const file = item.getAsFile();
                                    if (file) {
                                        // Prevent default paste behavior
                                        event.preventDefault();
                                        // Use the configured upload function
                                        this.options.upload(file).then((url) => {
                                            // Insert image into editor
                                            view.dispatch(
                                                view.state.tr.replaceSelectionWith(
                                                    this.type.create({ src: url })
                                                )
                                            );
                                        }).catch((error) => {
                                            console.error('Image paste upload failed:', error);
                                        });
                                        return true; // Handled
                                    }
                                }
                            }
                            return false; // Not handled, use default behavior
                        },
                    },
                }),
            ];
        },
    }).configure({
        upload(image) {
            const formData = new FormData();
            formData.append('image', image);

            return axios.post(route('api.upload-wysiwyg-image', { folder }), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then((response) => {
                return response.data.url;
            }).catch((error) => {
                const errorMessage = error.response?.data?.errors?.image?.[0] || 'Image upload failed';
                console.error('Image upload failed:', errorMessage);
                throw error;
            });
        },
    });
};

// Default Tiptap configuration
export const vuetifyProTipTap = createVuetifyProTipTap({
    lang: 'en',
    fallbackLang: 'en',
    components: {
        VuetifyTiptap,
        VuetifyViewer,
    },
    extensions: [
        ...extensions,
        imageExtension(), // Default folder
    ],
});
