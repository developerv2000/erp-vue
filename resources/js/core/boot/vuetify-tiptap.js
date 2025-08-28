import { createVuetifyProTipTap, VuetifyTiptap, VuetifyViewer } from "vuetify-pro-tiptap";
import { BaseKit, Bold, Italic, Underline, Strike, Color, Highlight, Heading, TextAlign, FontFamily, FontSize, SubAndSuperScript, BulletList, OrderedList, TaskList, Indent, Link, Image, Video, Table, Blockquote, HorizontalRule, Code, CodeBlock, Clear, Fullscreen, History } from 'vuetify-pro-tiptap'
import 'vuetify-pro-tiptap/style.css'
import axios from "axios";

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
    return Image.configure({
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
