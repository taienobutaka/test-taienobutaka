import { createApp } from "vue";
import UploadComponent from "./components/UploadComponent.vue";

const app = createApp({});
app.component("upload-component", UploadComponent);
app.mount("#app");
