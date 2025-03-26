<!-- filepath: /home/taie/work/test-taienobutaka/resources/js/components/UploadComponent.vue -->
<template>
    <div class="w-1/2 mx-auto text-center font-sans">
        <h1 class="mb-5 text-2xl font-bold">CSVアップロード</h1>
        <div v-if="successMessage" class="mb-3 text-green-500">
            {{ successMessage }}
        </div>
        <ul v-if="errors.length" class="mb-3 text-red-500 list-none">
            <li v-for="error in errors" :key="error">{{ error }}</li>
        </ul>

        <!-- ドラッグ&ドロップエリア -->
        <div
            id="drop-area"
            class="border-2 border-dashed border-blue-500 rounded p-5 mb-5 text-center cursor-pointer"
            @dragover.prevent="highlight"
            @dragleave.prevent="unhighlight"
            @drop.prevent="handleDrop"
        >
            <p class="mb-2">ここにCSVファイルをドラッグ&ドロップしてください</p>
            <p class="mb-2">または</p>
            <input
                type="file"
                ref="fileInput"
                accept=".csv"
                class="hidden"
                @change="handleFileSelect"
            />
            <button
                @click="triggerFileSelect"
                class="px-5 py-2 bg-blue-500 text-white rounded hover:bg-blue-700"
            >
                ファイルを選択
            </button>
            <p v-if="fileName" class="mt-3 italic">
                選択されたファイル: {{ fileName }}
                <button
                    @click="clearFile"
                    class="ml-2 text-red-500 hover:text-red-700"
                >
                    ×
                </button>
            </p>
        </div>

        <!-- アップロードフォーム -->
        <button
            @click="uploadFile"
            :disabled="!file"
            class="px-5 py-2 bg-blue-500 text-white rounded hover:bg-blue-700"
        >
            アップロード
        </button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            file: null,
            fileName: "",
            successMessage: "",
            errors: [],
        };
    },
    methods: {
        triggerFileSelect() {
            this.$refs.fileInput.click();
        },
        handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.setFile(files[0]);
            }
        },
        handleDrop(event) {
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.setFile(files[0]);
            }
        },
        setFile(file) {
            if (file.type === "text/csv") {
                this.file = file;
                this.fileName = file.name;
                this.errors = [];
            } else {
                this.errors = ["CSVファイルを選択してください。"];
                this.clearFile();
            }
        },
        clearFile() {
            this.file = null;
            this.fileName = "";
        },
        async uploadFile() {
            if (!this.file) return;

            const formData = new FormData();
            formData.append("csv_file", this.file);

            try {
                const response = await fetch("/upload/csv", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    this.errors = errorData.errors || [
                        "アップロードに失敗しました。",
                    ];
                } else {
                    this.successMessage = "アップロードが成功しました！";
                    this.clearFile();
                }
            } catch (error) {
                this.errors = ["アップロード中にエラーが発生しました。"];
            }
        },
        highlight() {
            document.getElementById("drop-area").classList.add("bg-blue-100");
        },
        unhighlight() {
            document
                .getElementById("drop-area")
                .classList.remove("bg-blue-100");
        },
    },
};
</script>

<style scoped>
#drop-area.bg-blue-100 {
    background-color: #f0f8ff;
}
</style>
