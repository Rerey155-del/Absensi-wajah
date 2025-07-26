<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Absensi Wajah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- Tailwind CSS + DaisyUI via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {},
            },
            plugins: [tailwindcss.plugin(({
                addComponents
            }) => {
                addComponents(require('https://cdn.jsdelivr.net/npm/daisyui@latest'));
            })],
        }
    </script>

    {{-- TensorFlow.js & BlazeFace --}}
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.14.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface@0.0.7"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-base-200 text-base-content font-sans min-h-screen">

    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-6 text-center">📷 Absensi Wajah (Deteksi Otomatis)</h2>

        {{-- Kamera --}}
        <div class="flex justify-center">
            <video id="video" width="640" height="480" autoplay muted
                class="rounded-lg shadow-lg border"></video>
            <canvas id="canvas" width="640" height="480" class="hidden"></canvas>
        </div>

        {{-- Preview --}}
        <div class="flex flex-col items-center mt-10 space-y-2">
            <h4 class="font-semibold mb-2">📸 Gambar Preview:</h4>
            <img id="preview" width="320" class="border rounded-lg shadow-md" alt="Preview akan muncul di sini" />
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const preview = document.getElementById('preview');

        let wajahTerdeteksi = false;
        let gambarBase64 = '';

        document.addEventListener('DOMContentLoaded', () => {
            const savedImage = localStorage.getItem('previewImage');
            if (savedImage) {
                preview.src = savedImage;
            }
        });

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
                await video.play();
            } catch (error) {
                Swal.fire('❌ Kamera Gagal!', 'Pastikan kamera Anda tersedia dan diizinkan.', 'error');
            }
        }

        async function detectFace() {
            const model = await blazeface.load();

            const detectOnce = async () => {
                if (wajahTerdeteksi) return;

                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const predictions = await model.estimateFaces(video, false);

                if (predictions.length > 0) {
                    wajahTerdeteksi = true;
                    gambarBase64 = canvas.toDataURL('image/png');
                    preview.src = gambarBase64;
                    video.pause();

                    Swal.fire({
                        title: '✍️ Masukkan Nama',
                        input: 'text',
                        inputLabel: 'Nama:',
                        inputPlaceholder: 'Contoh: Budi',
                        showCancelButton: true,
                        confirmButtonText: '💾 Simpan Absensi',
                        cancelButtonText: '❌ Batal',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        inputValidator: (value) => {
                            if (!value) return 'Nama tidak boleh kosong!';
                        },
                        didOpen: () => {
                            Swal.getInput().focus();
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            submitAbsensi(result.value.trim());
                        } else {
                            simpanPreviewLaluReload();
                        }
                    });
                } else {
                    setTimeout(detectOnce, 1000);
                }
            };

            detectOnce();
        }

        function submitAbsensi(nama) {
            fetch('/absensi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        nama,
                        gambar: gambarBase64
                    })
                })
                .then(res => res.json())
                .then(() => {
                    Swal.fire('✅ Sukses!', 'Absensi berhasil disimpan.', 'success')
                        .then(() => simpanPreviewLaluReload());
                })
                .catch(() => {
                    Swal.fire('❌ Gagal!', 'Terjadi kesalahan saat menyimpan absensi.', 'error')
                        .then(() => simpanPreviewLaluReload());
                });
        }

        function simpanPreviewLaluReload() {
            localStorage.setItem('previewImage', gambarBase64);
            location.reload();
        }

        startCamera().then(detectFace);
    </script>

</body>

</html>
