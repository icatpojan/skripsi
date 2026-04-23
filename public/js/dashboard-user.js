let map;
let markers = [];
let markerClusterGroup;
let reportLocationMap;
let reportLocationMarker;

// CSRF Token untuk AJAX
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Request location permission on page load
    requestLocationPermissionOnLoad();

    // Check if location permission was already granted
    if (localStorage.getItem("locationPermission") === "granted") {
        enableReportButton();
    }

    // Load waste types when page loads
    loadWasteTypes();

    // Initialize file upload functionality
    initFileUpload();

    // Modal events
    $("#reportModal").on("shown.bs.modal", function () {
        loadWasteTypes();
        // Reset file upload area
        const filePreview = document.getElementById("filePreview");
        const fileUploadArea = document.getElementById("fileUploadArea");
        const cameraSection = document.getElementById("cameraSection");
        const cameraButtonContainer = document.getElementById(
            "cameraButtonContainer"
        );
        if (filePreview && fileUploadArea) {
            filePreview.style.display = "none";
            fileUploadArea.style.display = "flex"; // Always show upload area on modal open
        }
        if (cameraSection) {
            cameraSection.style.display = "none";
        }
        if (cameraButtonContainer) {
            cameraButtonContainer.style.display = "block"; // Always show camera button on modal open
        }
    });

    $("#reportModal").on("hidden.bs.modal", function () {
        $("#reportForm")[0].reset();
        // Reset file upload area
        const filePreview = document.getElementById("filePreview");
        const fileUploadArea = document.getElementById("fileUploadArea");
        const cameraSection = document.getElementById("cameraSection");
        const cameraButtonContainer = document.getElementById(
            "cameraButtonContainer"
        );
        if (filePreview && fileUploadArea) {
            filePreview.style.display = "none";
            fileUploadArea.style.display = "flex"; // Always show upload area on modal close
        }
        if (cameraSection) {
            cameraSection.style.display = "none";
            // Stop camera if running
            const video = document.getElementById("cameraVideo");
            if (video && video.srcObject) {
                const tracks = video.srcObject.getTracks();
                tracks.forEach((track) => track.stop());
            }
        }
        if (cameraButtonContainer) {
            cameraButtonContainer.style.display = "block"; // Always show camera button on modal close
        }
    });

    // Submit form laporan
    $("#reportForm").on("submit", function (e) {
        e.preventDefault();
        console.log("Form submitted - preventing default");

        let formData = new FormData(this);

        // Show loading state
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.html();
        submitBtn.html(
            '<i class="fas fa-spinner fa-spin mr-1"></i>Mengirim...'
        );
        submitBtn.prop("disabled", true);

        // Debug form data
        console.log("=== FORM SUBMISSION DEBUG ===");
        console.log("Form data being sent:");
        for (let [key, value] of formData.entries()) {
            if (key === "image") {
                if (value.size > 0) {
                    console.log(
                        key + ":",
                        value.name,
                        "(File size: " + value.size + " bytes)"
                    );
                } else {
                    console.log(key + ": No file selected");
                }
            } else {
                console.log(key + ":", value);
            }
        }
        console.log(
            "CSRF Token:",
            $('meta[name="csrf-token"]').attr("content")
        );
        console.log("=============================");

        $.ajax({
            url: "/api/waste-reports",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            success: function (response) {
                console.log("=== SUCCESS RESPONSE ===");
                console.log("Response:", response);
                console.log("=======================");

                if (response.success) {
                    // Show success alert with better message
                    showAlert(
                        "success",
                        "🎉 Laporan sampah berhasil dikirim! Tim kami akan segera memproses laporan Anda."
                    );

                    // Close modal
                    $("#reportModal").modal("hide");

                    // Reset form
                    $("#reportForm")[0].reset();

                    // Update statistics
                    updateStatistics();

                    // Reload recent reports
                    loadRecentReports();
                } else {
                    showAlert(
                        "error",
                        response.message ||
                        "Terjadi kesalahan yang tidak diketahui"
                    );
                }
            },
            error: function (xhr, status, error) {
                console.log("=== ERROR RESPONSE ===");
                console.error("Error:", error);
                console.error("Status:", status);
                console.error("Response Text:", xhr.responseText);
                console.error("Status Code:", xhr.status);
                console.log("=====================");

                let errorMessage = "❌ Terjadi kesalahan. Silakan coba lagi.";

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Validation errors
                        let errorList = [];
                        Object.keys(xhr.responseJSON.errors).forEach(function (
                            key
                        ) {
                            xhr.responseJSON.errors[key].forEach(function (
                                error
                            ) {
                                errorList.push(error);
                            });
                        });
                        errorMessage = "❌ " + errorList.join("\n");
                    } else if (xhr.responseJSON.message) {
                        errorMessage = "❌ " + xhr.responseJSON.message;
                    }
                } else if (xhr.status === 419) {
                    errorMessage =
                        "❌ Session expired. Silakan refresh halaman dan coba lagi.";
                } else if (xhr.status === 500) {
                    errorMessage = "❌ Server error. Silakan coba lagi nanti.";
                }

                showAlert("error", errorMessage);
            },
            complete: function () {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop("disabled", false);
            },
        });
    });

    // Submit form profile
    $("#profileForm").on("submit", function (e) {
        e.preventDefault();

        let formData = $(this).serialize();

        // Show loading state
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.html();
        submitBtn.html(
            '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'
        );
        submitBtn.prop("disabled", true);

        console.log("Submitting profile data:", formData);

        $.ajax({
            url: "/api/profile",
            type: "PUT",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            success: function (response) {
                console.log("Profile update success:", response);
                if (response.success) {
                    $("#profileModal").modal("hide");
                    showAlert("success", response.message);

                    // Update user name in welcome message
                    $(".card-body h4").html(
                        `<i class="fas fa-hand-wave mr-2"></i>Selamat datang, ${response.user.name}!`
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error("Error updating profile:", error);
                console.error("Status:", status);
                console.error("Response:", xhr.responseText);
                console.error("Status Code:", xhr.status);

                let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                let errorMessage = "Terjadi kesalahan. Silakan coba lagi.";

                if (errors) {
                    errorMessage = Object.values(errors).flat().join("\n");
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showAlert("error", errorMessage);
            },
            complete: function () {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop("disabled", false);
            },
        });
    });
});

// Fungsi untuk membuka modal laporan
function openReportModal() {
    $("#reportModal").modal("show");
    // Load waste types when modal opens
    loadWasteTypes();
}

// Fungsi untuk membuka modal peta
function openMapModal() {
    $("#mapModal").modal("show");
    setTimeout(() => {
        initMap();
    }, 500);
}

// Fungsi untuk membuka modal profile
function openProfileModal() {
    $("#profileModal").modal("show");
}

// Load waste types
function loadWasteTypes() {
    console.log("Loading waste types...");
    $.ajax({
        url: "/api/waste-types",
        type: "GET",
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            Accept: "application/json",
        },
        success: function (data) {
            console.log("Waste types loaded successfully:", data);
            let options = '<option value="">Pilih Jenis Sampah</option>';
            data.forEach(function (type) {
                options += `<option value="${type.id}">${type.name}</option>`;
            });
            $("#waste_type_id").html(options);
        },
        error: function (xhr, status, error) {
            console.error("Failed to load waste types:", error);
            console.error("Status:", status);
            console.error("Response:", xhr.responseText);
            console.error("Status Code:", xhr.status);
        },
    });
}

// Get current location on page load (if permission already granted)
function getCurrentLocationOnLoad() {
    navigator.geolocation.getCurrentPosition(
        function (position) {
            console.log("Location obtained on page load:", position.coords);

            // Store coordinates
            localStorage.setItem("userLatitude", position.coords.latitude);
            localStorage.setItem("userLongitude", position.coords.longitude);
            localStorage.setItem("locationPermission", "granted");

            // Enable report button
            enableReportButton();

            // Get address from coordinates
            getAddressFromCoordinates(
                position.coords.latitude,
                position.coords.longitude
            );

            // Show success alert
            showAlert(
                "success",
                "✅ Lokasi berhasil diperoleh! Anda siap melaporkan sampah."
            );
        },
        function (error) {
            console.error("Error getting location on page load:", error);
            showAlert(
                "error",
                "❌ Gagal memperoleh lokasi saat halaman dibuka."
            );
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000, // 5 minutes
        }
    );
}

// Get address from coordinates
function getAddressFromCoordinates(latitude, longitude) {
    fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
    )
        .then((response) => response.json())
        .then((data) => {
            if (data.display_name) {
                localStorage.setItem("userAddress", data.display_name);
                // Update address field if modal is open
                if ($("#address").length) {
                    $("#address").val(data.display_name);
                }
                console.log("Address obtained:", data.display_name);
            }
        })
        .catch((error) => {
            console.error("Error getting address:", error);
        });
}

// Get current location with alert (for manual button)
function getCurrentLocationWithAlert() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                $("#latitude").val(position.coords.latitude);
                $("#longitude").val(position.coords.longitude);

                // Store coordinates
                localStorage.setItem("userLatitude", position.coords.latitude);
                localStorage.setItem(
                    "userLongitude",
                    position.coords.longitude
                );

                // Update map marker if map exists
                if (reportLocationMap && reportLocationMarker) {
                    const latlng = L.latLng(
                        position.coords.latitude,
                        position.coords.longitude
                    );
                    reportLocationMarker.setLatLng(latlng);
                    reportLocationMap.setView(latlng, 18);
                }

                // Reverse geocoding untuk mendapatkan alamat
                fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`
                )
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.display_name) {
                            $("#address").val(data.display_name);
                            localStorage.setItem(
                                "userAddress",
                                data.display_name
                            );
                        }
                        showAlert("success", "✅ Lokasi berhasil diperbarui!");
                    })
                    .catch((error) => {
                        console.error("Error getting address:", error);
                        showAlert(
                            "success",
                            "✅ Lokasi berhasil diperbarui! (Alamat tidak dapat diperoleh)"
                        );
                    });
            },
            function (error) {
                let errorMessage = "❌ Gagal memperoleh lokasi: ";
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += "Izin lokasi ditolak";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += "Informasi lokasi tidak tersedia";
                        break;
                    case error.TIMEOUT:
                        errorMessage += "Waktu tunggu habis";
                        break;
                    default:
                        errorMessage +=
                            "Terjadi kesalahan yang tidak diketahui";
                }
                showAlert("error", errorMessage);
            }
        );
    } else {
        showAlert("error", "❌ Geolokasi tidak didukung oleh browser ini.");
    }
}

// Get current location (for manual button) - legacy function
function getCurrentLocation() {
    getCurrentLocationWithAlert();
}

// Initialize report location map
function initReportLocationMap() {
    if (reportLocationMap) {
        reportLocationMap.remove();
    }

    // Get current location from localStorage or use default
    const latitude = localStorage.getItem("userLatitude") || -6.2088;
    const longitude = localStorage.getItem("userLongitude") || 106.8456;

    reportLocationMap = L.map("reportLocationMap").setView(
        [latitude, longitude],
        18
    );

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(reportLocationMap);

    // Add current location marker
    reportLocationMarker = L.marker([latitude, longitude], {
        draggable: true,
        title: "Lokasi Sampah",
    }).addTo(reportLocationMap);

    // Update form fields when marker is dragged
    reportLocationMarker.on("dragend", function (event) {
        const marker = event.target;
        const position = marker.getLatLng();

        // Update form fields
        $("#latitude").val(position.lat);
        $("#longitude").val(position.lng);

        // Update localStorage
        localStorage.setItem("userLatitude", position.lat);
        localStorage.setItem("userLongitude", position.lng);

        // Get address from new coordinates
        getAddressFromCoordinates(position.lat, position.lng);

        console.log("Marker moved to:", position.lat, position.lng);
    });

    // Allow clicking on map to move marker
    reportLocationMap.on("click", function (e) {
        const latlng = e.latlng;
        reportLocationMarker.setLatLng(latlng);

        // Update form fields
        $("#latitude").val(latlng.lat);
        $("#longitude").val(latlng.lng);

        // Update localStorage
        localStorage.setItem("userLatitude", latlng.lat);
        localStorage.setItem("userLongitude", latlng.lng);

        // Get address from new coordinates
        getAddressFromCoordinates(latlng.lat, latlng.lng);

        console.log("Map clicked at:", latlng.lat, latlng.lng);
    });
}

// Initialize map
function initMap() {
    if (map) {
        map.remove();
    }

    // Initialize marker cluster group
    markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: true,
        zoomToBoundsOnClick: true,
        iconCreateFunction: function (cluster) {
            var count = cluster.getChildCount();
            var c = " marker-cluster-";
            if (count < 10) {
                c += "small";
            } else if (count < 100) {
                c += "medium";
            } else {
                c += "large";
            }

            return new L.DivIcon({
                html: "<div><span>" + count + "</span></div>",
                className: "marker-cluster" + c,
                iconSize: new L.Point(40, 40),
            });
        },
    });

    map = L.map("map").setView([-6.2088, 106.8456], 10); // Default to Jakarta

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(map);

    // Add marker cluster group to map
    map.addLayer(markerClusterGroup);

    // Load reports for map
    loadReportsForMap();
}

// Load reports for map
function loadReportsForMap() {
    console.log("Loading user-specific reports for map...");
    $.get("/api/user/reports") // Updated to show only user's reports
        .done(function (data) {
            console.log("User map data received:", data.length, "reports");
            // Clear existing markers
            markerClusterGroup.clearLayers();
            markers = [];

            let latestReport = null;
            let latestDate = null;

            data.forEach(function (report) {
                if (!report.latitude || !report.longitude) return;

                // Determine marker color based on status (sync with admin)
                let markerColor = '#6c757d'; // secondary/default
                if (report.status === 'pending') markerColor = '#ffc107'; // yellow
                else if (report.status === 'processed') markerColor = '#17a2b8'; // info/blue
                else if (report.status === 'completed') markerColor = '#28a745'; // success/green
                else if (report.status === 'rejected') markerColor = '#dc3545'; // danger/red

                // Create custom colored circular icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${markerColor}; width: 22px; height: 22px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"></div>`,
                    iconSize: [22, 22],
                    iconAnchor: [11, 11]
                });

                let marker = L.marker([report.latitude, report.longitude], { icon: customIcon })
                    .bindPopup(`
                    <div style="min-width: 200px;">
                        <h6 class="mb-1 font-weight-bold">${report.title || "Laporan Sampah"}</h6>
                        <div class="mb-2">
                           <span class="badge badge-${getStatusBadgeClass(report.status)}">${report.status_text}</span>
                        </div>
                        ${report.image_path
                            ? `<img src="/storage/${report.image_path}" class="report-image mb-2" style="max-width: 100%; height: 100px; object-fit: cover; border-radius: 8px;">`
                            : ""
                        }
                        <p class="mb-1 small"><strong>Jenis:</strong> ${report.waste_type ? report.waste_type.name : "Tidak ditentukan"
                        }</p>
                        <p class="mb-0 small text-muted"><strong>Tanggal:</strong> ${new Date(
                            report.created_at
                        ).toLocaleDateString("id-ID", {
                            day: "numeric",
                            month: "short",
                            year: "numeric",
                        })}</p>
                        <button class="btn btn-primary btn-sm btn-block mt-2" onclick="viewReportDetail(${report.id})">
                            Detail
                        </button>
                    </div>
                `);

                // Add marker to cluster group
                markerClusterGroup.addLayer(marker);
                markers.push(marker);

                // Track latest report
                const reportDate = new Date(report.created_at);
                if (!latestDate || reportDate > latestDate) {
                    latestDate = reportDate;
                    latestReport = report;
                }
            });

            // Center map to latest report or user location
            if (latestReport) {
                map.setView([latestReport.latitude, latestReport.longitude], 17);
            } else {
                const userLat = localStorage.getItem("userLatitude");
                const userLng = localStorage.getItem("userLongitude");
                if (userLat && userLng) {
                    map.setView([userLat, userLng], 17);
                }
            }

            // Re-invalidate size to ensure map displays correctly
            setTimeout(() => map.invalidateSize(), 200);
        })
        .fail(function () {
            console.error("Gagal memuat data untuk peta");
        });
}

// Get status badge class
function getStatusBadgeClass(status) {
    switch (status) {
        case "pending":
            return "warning";
        case "processed":
            return "info";
        case "completed":
            return "success";
        case "rejected":
            return "danger";
        default:
            return "secondary";
    }
}

// View report detail
function viewReportDetail(id) {
    $.get(`/api/reports/${id}`)
        .done(function (data) {
            let content = `
                <div class="row">
                    ${data.image_path
                    ? `
                    <div class="col-md-6">
                        <img src="/storage/${data.image_path}" class="img-fluid rounded" alt="Foto Sampah">
                    </div>
                    `
                    : ""
                }
                    <div class="${data.image_path ? "col-md-6" : "col-md-12"}">
                        <h5>${data.title || "Laporan Sampah"}</h5>
                        ${data.description
                    ? `<p class="text-muted">${data.description}</p>`
                    : ""
                }
                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge badge-${getStatusBadgeClass(
                    data.status
                )}">${data.status_text}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Jenis Sampah:</strong>
                            ${data.waste_type
                    ? data.waste_type.name
                    : "Tidak ditentukan"
                }
                        </div>
                        <div class="mb-2">
                            <strong>Lokasi:</strong>
                            ${data.address || "Tidak ada alamat"}
                        </div>
                        <div class="mb-2">
                            <strong>Koordinat:</strong>
                            ${data.latitude}, ${data.longitude}
                        </div>
                        <div class="mb-2">
                            <strong>Tanggal:</strong>
                            ${new Date(data.created_at).toLocaleDateString(
                    "id-ID",
                    {
                        day: "numeric",
                        month: "short",
                        year: "numeric",
                    }
                )}
                        </div>
                        ${data.admin_notes
                    ? `
                            <div class="mb-2">
                                <strong>Catatan Admin:</strong>
                                <p class="text-muted">${data.admin_notes}</p>
                            </div>
                            `
                    : ""
                }
                        ${data.feedback
                    ? `
                            <div class="mb-2">
                                <strong>Feedback Admin:</strong>
                                <div class="alert alert-info">
                                    <p class="mb-0">${data.feedback}</p>
                                    ${data.image_feedback
                        ? `<img src="/storage/${data.image_feedback}" class="img-fluid rounded mt-2" alt="Foto Feedback" style="max-height: 200px;">`
                        : ""
                    }
                                </div>
                            </div>
                            `
                    : ""
                }
                    </div>
                </div>
            `;

            $("#reportDetailContent").html(content);
            $("#reportDetailModal").modal("show");
        })
        .fail(function () {
            showAlert("error", "Gagal memuat detail laporan");
        });
}

// Load all reports
function loadAllReports() {
    $.get("/api/user/reports")
        .done(function (data) {
            let table = `
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Jenis Sampah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach(function (report) {
                table += `
                    <tr>
                        <td>${new Date(report.created_at).toLocaleDateString(
                    "id-ID",
                    { day: "numeric", month: "short", year: "numeric" }
                )}</td>
                        <td>${report.title || "Laporan Sampah"}</td>
                        <td>${report.waste_type
                        ? report.waste_type.name
                        : "Tidak ditentukan"
                    }</td>
                        <td><span class="badge badge-${getStatusBadgeClass(
                        report.status
                    )}">${report.status_text}</span></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="viewReportDetail(${report.id
                    })">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            table += `
                        </tbody>
                    </table>
                </div>
            `;

            $("#recent-reports-container").html(table);
        })
        .fail(function () {
            showAlert("error", "Gagal memuat semua laporan");
        });
}

// Load recent reports
function loadRecentReports() {
    $.get("/api/user/reports")
        .done(function (data) {
            console.log("User reports data received:", data);
            console.log("First user report:", data[0]);

            let recentData = data.slice(0, 5);
            let table = `
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Jenis Sampah</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            recentData.forEach(function (report) {
                table += `
                    <tr>
                        <td>${new Date(report.created_at).toLocaleDateString(
                    "id-ID",
                    { day: "numeric", month: "short", year: "numeric" }
                )}</td>
                        <td>${report.title || "Laporan Sampah"}</td>
                        <td>${report.waste_type
                        ? report.waste_type.name
                        : "Tidak ditentukan"
                    }</td>
                        <td><span class="badge badge-${getStatusBadgeClass(
                        report.status
                    )}">${report.status_text}</span></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="viewReportDetail(${report.id
                    })">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            table += `
                        </tbody>
                    </table>
                </div>
            `;

            $("#recent-reports-container").html(table);
        })
        .fail(function () {
            console.error("Gagal memuat laporan terbaru");
        });
}

// Update statistics
function updateStatistics() {
    $.get("/api/user/reports")
        .done(function (data) {
            let stats = {
                total: data.length,
                pending: data.filter((r) => r.status === "pending").length,
                processed: data.filter((r) => r.status === "processed").length,
                completed: data.filter((r) => r.status === "completed").length,
            };

            $("#total-reports").text(stats.total);
            $("#pending-reports").text(stats.pending);
            $("#processed-reports").text(stats.processed);
            $("#completed-reports").text(stats.completed);
        })
        .fail(function () {
            console.error("Gagal memuat statistik");
        });
}

// Request location permission on page load
function requestLocationPermissionOnLoad() {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        alert(
            "Geolokasi tidak didukung oleh browser ini. Anda tidak dapat melaporkan sampah."
        );
        return;
    }

    // Check if permission is already granted
    if (navigator.permissions) {
        navigator.permissions
            .query({ name: "geolocation" })
            .then(function (result) {
                if (result.state === "granted") {
                    console.log("Location permission already granted");
                    // Get current location if permission already granted
                    getCurrentLocationOnLoad();
                    return;
                } else if (result.state === "denied") {
                    showLocationDeniedModal();
                } else {
                    showLocationPermissionModal();
                }
            });
    } else {
        // Fallback for browsers that don't support permissions API
        showLocationPermissionModal();
    }
}

// Show location permission modal
function showLocationPermissionModal() {
    $("#locationPermissionModal").modal("show");
}

// Show location denied modal
function showLocationDeniedModal() {
    $("#locationDeniedModal").modal("show");
}

// Request location permission
function requestLocationPermission() {
    $("#locationPermissionModal").modal("hide");

    navigator.geolocation.getCurrentPosition(
        function (position) {
            console.log("Location permission granted");
            console.log("Position:", position.coords);

            // Store location permission status and coordinates
            localStorage.setItem("locationPermission", "granted");
            localStorage.setItem("userLatitude", position.coords.latitude);
            localStorage.setItem("userLongitude", position.coords.longitude);

            // Enable report button
            enableReportButton();

            // Show success alert
            showAlert(
                "success",
                "✅ Lokasi berhasil diperoleh! Anda sekarang dapat melaporkan sampah."
            );

            // Get address from coordinates
            getAddressFromCoordinates(
                position.coords.latitude,
                position.coords.longitude
            );
        },
        function (error) {
            console.error("Location permission denied:", error);
            localStorage.setItem("locationPermission", "denied");
            showLocationDeniedModal();

            // Show error alert
            let errorMessage = "❌ Gagal memperoleh lokasi: ";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += "Izin lokasi ditolak";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Informasi lokasi tidak tersedia";
                    break;
                case error.TIMEOUT:
                    errorMessage += "Waktu tunggu habis";
                    break;
                default:
                    errorMessage += "Terjadi kesalahan yang tidak diketahui";
            }
            showAlert("error", errorMessage);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000, // 5 minutes
        }
    );
}

// Enable report button
function enableReportButton() {
    console.log("Enabling report button...");
    const btn = $(".btn-report");
    btn.prop("disabled", false);
    btn.removeClass("btn-secondary disabled").addClass("btn-success");
    // Ensure the text color stays white and visibility is clear
    btn.css({
        "opacity": "1",
        "cursor": "pointer",
        "background-color": "#28a745"
    });
}

// Disable report button
function disableReportButton() {
    console.log("Disabling report button...");
    const btn = $(".btn-report");
    btn.prop("disabled", true);
    btn.removeClass("btn-success").addClass("btn-secondary disabled");
    btn.css({
        "opacity": "0.65",
        "cursor": "not-allowed"
    });
}

// Check location permission before opening report modal
function openReportModal() {
    if (localStorage.getItem("locationPermission") === "granted") {
        $("#reportModal").modal("show");
        loadWasteTypes();

        // Initialize report location map after modal is shown
        $("#reportModal").on("shown.bs.modal", function () {
            setTimeout(() => {
                initReportLocationMap();
            }, 100);
        });

        // Auto-fill location fields
        setTimeout(() => {
            const latitude = localStorage.getItem("userLatitude");
            const longitude = localStorage.getItem("userLongitude");
            const address = localStorage.getItem("userAddress");

            if (latitude && longitude) {
                $("#latitude").val(latitude);
                $("#longitude").val(longitude);
                console.log("Auto-filled coordinates:", latitude, longitude);
            }

            if (address) {
                $("#address").val(address);
                console.log("Auto-filled address:", address);
            }
        }, 500);
    } else {
        showLocationPermissionModal();
    }
}

// Initialize file upload functionality
function initFileUpload() {
    const fileUploadArea = document.getElementById("fileUploadArea");
    const fileInput = document.getElementById("image");
    const filePreview = document.getElementById("filePreview");
    const imagePreview = document.getElementById("imagePreview");

    if (!fileUploadArea || !fileInput) return;

    // Drag and drop functionality
    fileUploadArea.addEventListener("dragover", function (e) {
        e.preventDefault();
        fileUploadArea.classList.add("dragover");
    });

    fileUploadArea.addEventListener("dragleave", function (e) {
        e.preventDefault();
        fileUploadArea.classList.remove("dragover");
    });

    fileUploadArea.addEventListener("drop", function (e) {
        e.preventDefault();
        fileUploadArea.classList.remove("dragover");

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener("change", function (e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Click on upload area
    fileUploadArea.addEventListener("click", function (e) {
        // Don't trigger if clicking on buttons
        if (e.target.closest("button")) {
            return;
        }
        // Default to file selection
        selectFile();
    });

    // Initialize camera snap button
    const snapBtn = document.getElementById("snapBtn");
    if (snapBtn) {
        snapBtn.addEventListener("click", takePhoto);
    }
}

// Handle file selection
function handleFileSelect(file) {
    const fileInput = document.getElementById("image");
    const filePreview = document.getElementById("filePreview");
    const imagePreview = document.getElementById("imagePreview");
    const fileUploadArea = document.getElementById("fileUploadArea");

    // Validate file type
    if (!file.type.startsWith("image/")) {
        showAlert("error", "❌ File harus berupa gambar");
        return;
    }

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        showAlert("error", "❌ Ukuran file maksimal 2MB");
        return;
    }

    // Set file to input
    fileInput.files = new DataTransfer().files;
    fileInput.files = new DataTransfer().items.add(file).files;

    // Show preview
    const reader = new FileReader();
    reader.onload = function (e) {
        imagePreview.src = e.target.result;
        fileUploadArea.style.display = "none";
        filePreview.style.display = "block";

        // Hide camera button
        const cameraButtonContainer = document.getElementById(
            "cameraButtonContainer"
        );
        if (cameraButtonContainer) {
            cameraButtonContainer.style.display = "none";
        }
    };
    reader.readAsDataURL(file);

    showAlert("success", "✅ Foto berhasil dipilih");
}

// Remove image
function removeImage() {
    const fileInput = document.getElementById("image");
    const filePreview = document.getElementById("filePreview");
    const fileUploadArea = document.getElementById("fileUploadArea");
    const cameraSection = document.getElementById("cameraSection");

    fileInput.value = "";
    filePreview.style.display = "none";
    fileUploadArea.style.display = "flex"; // Show upload area again

    // Show camera button again
    const cameraButtonContainer = document.getElementById(
        "cameraButtonContainer"
    );
    if (cameraButtonContainer) {
        cameraButtonContainer.style.display = "block";
    }

    // Also close camera if it's open
    if (cameraSection) {
        cameraSection.style.display = "none";
        const video = document.getElementById("cameraVideo");
        if (video && video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach((track) => track.stop());
        }
    }
}

// Select file from gallery
function selectFile() {
    const fileInput = document.getElementById("image");
    fileInput.removeAttribute("capture");
    fileInput.click();
}

// Open camera
function openCamera() {
    const cameraSection = document.getElementById("cameraSection");
    const fileUploadArea = document.getElementById("fileUploadArea");

    if (cameraSection && fileUploadArea) {
        // Hide file upload area
        fileUploadArea.style.display = "none";
        // Show camera section
        cameraSection.style.display = "block";

        // Start camera
        startCamera();
    }
}

// Close camera
function closeCamera() {
    const cameraSection = document.getElementById("cameraSection");
    const fileUploadArea = document.getElementById("fileUploadArea");
    const video = document.getElementById("cameraVideo");

    if (cameraSection && fileUploadArea) {
        // Stop camera stream
        if (video && video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach((track) => track.stop());
        }

        // Hide camera section
        cameraSection.style.display = "none";
        // Show file upload area
        fileUploadArea.style.display = "flex"; // Show upload area when camera is closed

        // Show camera button again
        const cameraButtonContainer = document.getElementById(
            "cameraButtonContainer"
        );
        if (cameraButtonContainer) {
            cameraButtonContainer.style.display = "block";
        }
    }
}

// Start camera
function startCamera() {
    const video = document.getElementById("cameraVideo");

    if (video) {
        navigator.mediaDevices
            .getUserMedia({
                video: {
                    facingMode: "environment", // Use back camera
                },
            })
            .then((stream) => {
                video.srcObject = stream;
            })
            .catch((err) => {
                console.error("Gagal akses kamera:", err);
                showAlert("error", "❌ Gagal akses kamera: " + err.message);
                closeCamera();
            });
    }
}

// Take photo
function takePhoto() {
    const video = document.getElementById("cameraVideo");
    const fileInput = document.getElementById("image");
    const filePreview = document.getElementById("filePreview");
    const imagePreview = document.getElementById("imagePreview");
    const cameraSection = document.getElementById("cameraSection");
    const fileUploadArea = document.getElementById("fileUploadArea");

    if (video && fileInput) {
        // Create canvas
        const canvas = document.createElement("canvas");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame to canvas
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0);

        // Convert canvas to blob
        canvas.toBlob(
            function (blob) {
                // Create file from blob
                const file = new File([blob], "camera-photo.jpg", {
                    type: "image/jpeg",
                });

                // Set file to input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                // Show preview
                imagePreview.src = canvas.toDataURL("image/jpeg");
                filePreview.style.display = "block";
                cameraSection.style.display = "none";
                fileUploadArea.style.display = "none"; // Hide upload area completely

                // Hide camera button
                const cameraButtonContainer = document.getElementById(
                    "cameraButtonContainer"
                );
                if (cameraButtonContainer) {
                    cameraButtonContainer.style.display = "none";
                }

                // Stop camera
                if (video.srcObject) {
                    const tracks = video.srcObject.getTracks();
                    tracks.forEach((track) => track.stop());
                }

                showAlert("success", "✅ Foto berhasil diambil!");
            },
            "image/jpeg",
            0.8
        );
    }
}

// Show alert - using global function from layout
function showAlert(type, message) {
    // Use the global showAlert function defined in layout
    if (typeof window.showAlert === 'function') {
        window.showAlert(type, message);
    } else {
        // Fallback for compatibility
        const alertClass =
            type === "success"
                ? "alert-success"
                : type === "error"
                    ? "alert-danger"
                    : type === "warning"
                        ? "alert-warning"
                        : "alert-info";
        const icon =
            type === "success"
                ? "fas fa-check-circle"
                : type === "error"
                    ? "fas fa-exclamation-circle"
                    : type === "warning"
                        ? "fas fa-exclamation-triangle"
                        : "fas fa-info-circle";

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show auto-dismiss" role="alert">
                <div class="d-flex align-items-center">
                    <i class="${icon} mr-2"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        // Remove any existing alerts first
        $("#alertContainer").empty();

        // Add new alert to the alert container
        $("#alertContainer").html(alertHtml);

        // Auto dismiss after 3 seconds
        setTimeout(function () {
            $("#alertContainer .alert").addClass('fade-out');
            setTimeout(function () {
                $("#alertContainer .alert").remove();
            }, 300);
        }, 3000);
    }
}
