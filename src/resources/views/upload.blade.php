<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
    <title>CSVアップロード</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div id="app" class="w-1/2 mx-auto text-center font-sans">
        <h1 class="mb-5 text-2xl font-bold">CSVアップロード</h1>
        <div v-if="successMessage" class="mb-3 text-green-500">@{{ successMessage }}</div>
        <ul v-if="errors.length" class="mb-3 text-red-500 list-none">
            <li v-for="error in errors" :key="error">@{{ error }}</li>
        </ul>

        <!-- ドラッグ&ドロップエリア -->
        <div 
            id="drop-area" 
            class="border-2 border-dashed border-blue-500 rounded p-5 mb-5 text-center cursor-pointer"
            @dragover.prevent="onDragOver"
            @dragleave="onDragLeave"
            @drop.prevent="onDrop"
            :class="{ 'bg-blue-100': isDragging }"
        >
            <p class="mb-2">ここにCSVファイルをドラッグ&ドロップしてください</p>
            <p class="mb-2">または</p>
            <input type="file" id="fileElem" accept=".csv" class="hidden" @change="onFileChange" ref="fileInput" multiple>
            <button @click="triggerFileInput" class="px-5 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">ファイルを選択</button>
            <p id="fileName" class="mt-3 italic" v-if="fileName">
                選択されたファイル: @{{ fileName }}
                <button @click="clearFile" class="ml-2 text-red-500 hover:text-red-700">×</button>
            </p>
        </div>

        <!-- 選択されたファイルのリスト -->
        <div v-if="files.length" class="mt-3">
            <h3 class="text-lg font-bold">選択されたファイル:</h3>
            <ul class="list-disc list-inside">
                <li v-for="(file, index) in files" :key="index">
                    @{{ file.name }}
                    <button @click="removeFile(index)" class="ml-2 text-red-500 hover:text-red-700">×</button>
                </li>
            </ul>
        </div>

        <!-- アップロードフォーム -->
        <form id="uploadForm" @submit.prevent="uploadFile" class="flex flex-col items-center">
            <button type="submit" class="px-5 py-2 bg-blue-500 text-white rounded hover:bg-blue-700" :disabled="!files.length">
                アップロード
            </button>
        </form>
    </div>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    files: [], // 複数のファイルを格納する配列
                    isDragging: false,
                    successMessage: '',
                    errors: []
                };
            },
            methods: {
                triggerFileInput() {
                    this.$refs.fileInput.click();
                },
                onFileChange(event) {
                    const files = event.target.files;
                    this.handleFiles(files);
                },
                onDragOver() {
                    this.isDragging = true;
                },
                onDragLeave() {
                    this.isDragging = false;
                },
                onDrop(event) {
                    this.isDragging = false;
                    const files = event.dataTransfer.files;
                    this.handleFiles(files);
                },
                handleFiles(files) {
                    this.errors = [];
                    for (const file of files) {
                        if (file && file.type === 'text/csv') {
                            this.files.push(file); // 配列に追加
                        } else {
                            this.errors.push(`${file.name} はCSVファイルではありません。`);
                        }
                    }
                },
                clearFile() {
                    this.files = [];
                },
                removeFile(index) {
                    this.files.splice(index, 1); // 指定されたインデックスのファイルを削除
                },
                async uploadFile() {
                    if (!this.files.length) {
                        this.errors = ['ファイルを選択してください。'];
                        return;
                    }

                    const formData = new FormData();
                    this.files.forEach((file, index) => {
                        formData.append(`csv_files[${index}]`, file); // 複数のファイルを送信
                    });

                    try {
                        const response = await fetch('{{ route('upload.csv') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.successMessage = data.message || 'アップロードが成功しました！';
                            this.files = []; // ファイルリストをクリア
                        } else {
                            const errorData = await response.json();
                            this.errors = errorData.errors || ['アップロードに失敗しました。'];
                        }
                    } catch (error) {
                        this.errors = ['エラーが発生しました。もう一度お試しください。'];
                    }
                }
            }
        });

        app.mount('#app');
    </script>
</body>
</html>