$(document).ready(function () {
    console.log("DASHBOARD ADMIN JS LOADED - VERSION 14:35");
    // alert("DASHBOARD ADMIN JS LOADED - VERSION 14:35");
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Load initial data
    loadAllReports();
    updateStatistics();
    loadWasteTypesForFilter();

    // Initialize event listeners
    initializeEventListeners();

    // Initialize dashboard map
    initDashboardMap();
});

// Global variables
let map;
let markers = [];
let markerClusterGroup;
let reportDetailMap;
let reportDetailMarker;
let dashboardMap = null;
let dashboardMarkers = [];
let dashboardDistricts = [];
window.watchID = null;
window.isGeolocated = false;
window.currentLocationMarker = null;
window.routingControl = null;

// Pagination variables
let currentUserPage = 1;
let currentReportPage = 1;
let userPerPage = 10;
let reportPerPage = 10;
let totalUsers = 0;
let totalReports = 0;

// Filter variables
let userFilters = {
    search: "",
    role: "",
    status: "",
};

let reportFilters = {
    search: "",
    reporter: "",
    status: "",
    type: "",
    perPage: 10,
    dateStart: "",
    dateEnd: "",
    feedback: "",
};

// District management variables
let currentDistrictPage = 1;
let districtFilters = {
    search: "",
    status: "",
    perPage: 10,
};
let districtMap = null;
let districtPolygon = null;
let districtPolygonPoints = [];
let districtMarkers = [];
let existingDistrictLayers = [];

// Initialize event listeners
function initializeEventListeners() {
    // User search and filter
    $("#userSearch").on("input", function () {
        userFilters.search = $(this).val();
        currentUserPage = 1;
        loadUsers();
    });

    $("#userRoleFilter").on("change", function () {
        userFilters.role = $(this).val();
        currentUserPage = 1;
        loadUsers();
    });

    $("#userStatusFilter").on("change", function () {
        userFilters.status = $(this).val();
        currentUserPage = 1;
        loadUsers();
    });

    // Report search and filter
    $("#reportSearch").on("input", function () {
        reportFilters.search = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportReporterFilter").on("input", function () {
        reportFilters.reporter = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportStatusFilter").on("change", function () {
        reportFilters.status = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportTypeFilter").on("change", function () {
        reportFilters.type = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportPerPage").on("change", function () {
        reportFilters.perPage = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportFeedbackFilter").on("change", function () {
        reportFilters.feedback = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    // Date filters
    $("#reportDateStart").on("change", function () {
        reportFilters.dateStart = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    $("#reportDateEnd").on("change", function () {
        reportFilters.dateEnd = $(this).val();
        currentReportPage = 1;
        loadAllReports();
    });

    // District search and filter
    $("#districtSearch").on("input", function () {
        districtFilters.search = $(this).val();
        currentDistrictPage = 1;
        loadAllDistricts();
    });

    $("#districtStatusFilter").on("change", function () {
        districtFilters.status = $(this).val();
        currentDistrictPage = 1;
        loadAllDistricts();
    });

    $("#districtPerPage").on("change", function () {
        districtFilters.perPage = $(this).val();
        currentDistrictPage = 1;
        loadAllDistricts();
    });

    // Clean up report detail map when modal is closed
    $("#reportDetailModal").on("hidden.bs.modal", function () {
        if (reportDetailMap) {
            reportDetailMap.remove();
            reportDetailMap = null;
            reportDetailMarker = null;
        }
    });

    // Fix for nested modals scroll issue
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal:visible').length > 0) {
            $('body').addClass('modal-open');
        }
    });
}

// Show alert function - using global function from layout
function showAlert(type, message) {
    // Use the global showAlert function defined in layout
    if (typeof window.showAlert === "function") {
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
            $("#alertContainer .alert").addClass("fade-out");
            setTimeout(function () {
                $("#alertContainer .alert").remove();
            }, 300);
        }, 3000);
    }
}

// Open map modal
function openMapModal() {
    $("#mapModal").modal("show");
    setTimeout(() => {
        initMap();
    }, 500);
}

// Initialize map
function initMap() {
    if (map) {
        map.remove();
    }

    markerClusterGroup = L.markerClusterGroup({
        iconCreateFunction: function (cluster) {
            const count = cluster.getChildCount();
            let size, className;

            if (count < 10) {
                size = "small";
                className = "marker-cluster-small";
            } else if (count < 100) {
                size = "medium";
                className = "marker-cluster-medium";
            } else {
                size = "large";
                className = "marker-cluster-large";
            }

            return L.divIcon({
                html: "<div><span>" + count + "</span></div>",
                className: "marker-cluster " + className,
                iconSize: L.point(40, 40),
            });
        },
    });

    map = L.map("map").setView([-6.2088, 106.8456], 10); // Default to Jakarta
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(map);

    map.addLayer(markerClusterGroup);
    loadReportsForMap();
}

// Initialize dashboard map
function initDashboardMap() {
    if (!document.getElementById('dashboardMap')) {
        return;
    }

    if (dashboardMap) {
        dashboardMap.remove();
    }

    // Small delay to ensure container is rendered
    setTimeout(function () {
        // Create marker cluster group
        window.dashboardMarkerCluster = L.markerClusterGroup({
            iconCreateFunction: function (cluster) {
                const count = cluster.getChildCount();
                let size, className;

                if (count < 10) {
                    size = "small";
                    className = "marker-cluster-small";
                } else if (count < 100) {
                    size = "medium";
                    className = "marker-cluster-medium";
                } else {
                    size = "large";
                    className = "marker-cluster-large";
                }

                return L.divIcon({
                    html: "<div><span>" + count + "</span></div>",
                    className: "marker-cluster " + className,
                    iconSize: L.point(40, 40),
                });
            }
        });

        window.dashboardMap = L.map("dashboardMap").setView([-6.333829, 106.718393], 13); // Center to the specific area relocated earlier
        dashboardMap = window.dashboardMap;
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(dashboardMap);

        // Add a Locate Me button to the map - simplified
        const LocateControl = L.Control.extend({
            onAdd: function (map) {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.style.backgroundColor = 'white';
                container.style.width = '34px';
                container.style.height = '34px';
                container.style.display = 'flex';
                container.style.alignItems = 'center';
                container.style.justifyContent = 'center';
                container.style.cursor = 'pointer';
                container.innerHTML = '<i class="fas fa-crosshairs" style="font-size: 18px; color: #4285F4;"></i>';
                container.title = "Cari Lokasi Saya";

                L.DomEvent.disableClickPropagation(container);
                container.onclick = function (e) {
                    L.DomEvent.stopPropagation(e);
                    console.log("Tombol lokasi diklik secara manual");
                    requestGeolocation(true);
                };
                return container;
            }
        });
        dashboardMap.addControl(new LocateControl({ position: 'topleft' }));

        // Geolocation Support - Auto start
        requestGeolocation(false);

        dashboardMap.addLayer(window.dashboardMarkerCluster);

        // Load districts first, then active reports
        loadDashboardDistricts(function () {
            loadDashboardActiveReports(window.dashboardMarkerCluster);
        });

        // Force map to recalculate size
        setTimeout(function () {
            if (dashboardMap) {
                dashboardMap.invalidateSize();
            }
        }, 100);
    }, 100);
}

// Load districts for dashboard map
function loadDashboardDistricts(callback) {
    const map = dashboardMap || window.dashboardMap;
    if (!map) {
        if (callback) callback();
        return;
    }

    // Remove existing district layers
    dashboardDistricts.forEach(function (layer) {
        map.removeLayer(layer);
    });
    dashboardDistricts = [];

    $.get("/api/admin/districts", { per_page: 1000 })
        .done(function (response) {
            if (response.data && response.data.length > 0) {
                response.data.forEach(function (district) {
                    if (!district.boundaries || district.boundaries.length < 3) {
                        return;
                    }

                    // Convert [lng, lat] to [lat, lng] for Leaflet
                    const polygonCoords = district.boundaries.map(function (point) {
                        return [point[1], point[0]];
                    });

                    // Create polygon
                    const districtLayer = L.polygon(polygonCoords, {
                        color: district.color || "#667eea",
                        fillColor: district.color || "#667eea",
                        fillOpacity: 0.2,
                        weight: 2,
                        opacity: 0.7
                    }).addTo(map);

                    // Add tooltip with district name
                    districtLayer.bindTooltip(district.name, {
                        permanent: false,
                        direction: "center",
                        className: "existing-district-tooltip"
                    });

                    dashboardDistricts.push(districtLayer);
                });
            }

            if (callback) callback();
        })
        .fail(function (xhr) {
            console.error("Error loading districts:", xhr);
            if (callback) callback();
        });
}

// Load active reports (pending/processed) for dashboard map
function loadDashboardActiveReports(markerCluster) {
    const map = dashboardMap || window.dashboardMap;
    if (!map) return;

    // Clear existing markers
    markerCluster.clearLayers();
    dashboardMarkers = [];

    $.get("/api/reports-for-map")
        .done(function (data) {
            // Filter only active reports (pending or processed)
            const activeReports = data.filter(function (report) {
                return report.status === 'pending' || report.status === 'processed';
            });

            if (activeReports.length === 0) {
                return;
            }

            activeReports.forEach(function (report) {
                console.log("Adding marker for report:", report.id, report.latitude, report.longitude);
                // Determine marker color based on status
                let markerColor = '#ffc107'; // yellow for pending
                if (report.status === 'processed') {
                    markerColor = '#17a2b8'; // blue for processed
                }

                // Create custom icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // Calculate initial distance if location active
                let distanceStr = "Mencari lokasi...";
                if (window.currentLocationMarker) {
                    const currentLoc = window.currentLocationMarker.getLatLng();
                    const dist = L.latLng(currentLoc.lat, currentLoc.lng).distanceTo([report.latitude, report.longitude]);
                    distanceStr = dist > 1000 ? (dist / 1000).toFixed(2) + " km" : Math.round(dist) + " m";
                }

                let marker = L.marker([report.latitude, report.longitude], { icon: customIcon })
                    .bindPopup(`
                      <div style="min-width: 280px; font-size: 0.85rem;">
                          <h6 class="font-weight-bold mb-1" style="font-size: 0.95rem;">${report.title || "Laporan Sampah"}</h6>
                          <div class="d-flex mb-2" style="gap: 10px; align-items: start;">
                              ${report.image_url ?
                            `<div style="flex: 0 0 80px;">
                                    <img src="${report.image_url}" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #eee;">
                                 </div>` : ""}
                              <div style="flex: 1; line-height: 1.2;">
                                  <p class="mb-1"><strong>Jarak:</strong> <span class="distance-value">${distanceStr}</span></p>
                                  <p class="mb-1"><strong>Status:</strong> <span class="badge badge-${getStatusBadgeClass(report.status)}" style="font-size: 0.7rem; padding: 2px 6px;">${report.status_text || report.status}</span></p>
                                  <p class="mb-1"><strong>Jenis:</strong> ${report.waste_type || "-"}</p>
                                  <p class="mb-0 text-muted" style="font-size: 0.75rem;">${report.user_name || "Anonim"} • ${new Date(report.created_at).toLocaleDateString("id-ID", { day: "numeric", month: "short" })}</p>
                              </div>
                          </div>
                          <div class="d-flex" style="gap: 5px;">
                              <button class="btn btn-sm btn-primary flex-grow-1" style="font-size: 0.75rem; padding: 4px;" onclick="showRouteToReport(${report.latitude}, ${report.longitude})">
                                  <i class="fas fa-directions mr-1"></i> Rute
                              </button>
                              <button class="btn btn-sm btn-info flex-grow-1" style="font-size: 0.75rem; padding: 4px;" onclick="openChangeStatusModal('${report.id}', '${report.status}')">
                                  <i class="fas fa-edit mr-1"></i> Status
                              </button>
                          </div>
                      </div>
                  `);

                marker.on('click', function () {
                    console.log("Marker clicked, showing route to:", report.latitude, report.longitude);
                    showRouteToReport(report.latitude, report.longitude);
                });

                markerCluster.addLayer(marker);
                dashboardMarkers.push(marker);
            });

            // Fit map to show all districts and markers after a short delay
            setTimeout(function () {
                const currentMap = dashboardMap || window.dashboardMap;
                if (currentMap && !window.isGeolocated && (dashboardDistricts.length > 0 || dashboardMarkers.length > 0)) {
                    const bounds = L.latLngBounds([]);

                    dashboardDistricts.forEach(function (layer) {
                        bounds.extend(layer.getBounds());
                    });

                    dashboardMarkers.forEach(function (marker) {
                        bounds.extend(marker.getLatLng());
                    });

                    if (bounds.isValid()) {
                        currentMap.fitBounds(bounds, { padding: [50, 50] });
                    }
                }
            }, 1000); // Increase delay to let geolocation work first
        })
        .fail(function () {
            console.error("Gagal memuat laporan aktif untuk peta");
        });
}

// Show route to report from current location
function showRouteToReport(destLat, destLng) {
    console.log("DEBUG: showRouteToReport called with:", destLat, destLng);

    // Ensure coordinates are numbers
    destLat = parseFloat(destLat);
    destLng = parseFloat(destLng);

    const map = window.dashboardMap || dashboardMap;
    if (!map) {
        console.error("Dashboard map not found");
        return;
    }

    // Check if location is available
    if (!window.currentLocationMarker) {
        console.warn("Current location marker not found");
        showAlert("warning", "Lokasi Anda belum ditemukan. Silakan klik tombol 'Cari Lokasi Saya' pada peta.");
        requestGeolocation(true);
        return;
    }

    const currentLoc = window.currentLocationMarker.getLatLng();
    console.log("Current location:", currentLoc.lat, currentLoc.lng);

    // Remove existing routing control if any
    if (window.routingControl) {
        console.log("Removing existing routing control");
        map.removeControl(window.routingControl);
        window.routingControl = null;
    }

    // Add routing control
    console.log("Creating new routing control...");
    try {
        window.routingControl = L.Routing.control({
            waypoints: [
                L.latLng(currentLoc.lat, currentLoc.lng),
                L.latLng(destLat, destLng)
            ],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: false,
            show: false, // Hide itinerary by default
            lineOptions: {
                styles: [{ color: '#3b82f6', opacity: 0.8, weight: 6 }]
            },
            createMarker: function () { return null; }
        }).addTo(map);

        // Error handling for routing
        window.routingControl.on('routingerror', function (e) {
            console.error("Routing error:", e.error);
            showAlert("error", "Gagal mencari rute jalan: " + (e.error.message || "Pastikan Anda terhubung ke internet"));
        });

        window.routingControl.on('routesfound', function (e) {
            console.log("Route found successfully:", e.routes[0]);
            showAlert("success", "Rute ditemukan! Menuju lokasi sampah.");
        });

        // Ensure instructions are hidden
        setTimeout(() => {
            const container = window.routingControl.getContainer();
            if (container) container.style.display = 'none';
        }, 100);

        showAlert("info", "Sedang mencari rute terbaik...");
    } catch (error) {
        console.error("Exception in showRouteToReport:", error);
    }
}

// Make globally available
window.showRouteToReport = showRouteToReport;

// Load reports for map
function loadReportsForMap() {
    $.get("/api/reports-for-map")
        .done(function (data) {
            markerClusterGroup.clearLayers();
            markers = [];
            let latestReport = null;
            let latestDate = null;

            data.forEach(function (report) {
                let marker = L.marker([report.latitude, report.longitude])
                    .bindPopup(`
                      <div style="min-width: 200px;">
                          <h6>${report.title || "Laporan Sampah"}</h6>
                          ${report.description
                            ? `<p class="mb-2">${report.description}</p>`
                            : ""
                        }
                          ${report.image_url
                            ? `<img src="${report.image_url}" class="report-image mb-2" style="max-width: 100%; height: 100px; object-fit: cover;">`
                            : ""
                        }
                          <p class="mb-1"><strong>Status:</strong> <span class="badge badge-${getStatusBadgeClass(
                            report.status
                        )}">${report.status_text}</span></p>
                          <p class="mb-1"><strong>Jenis:</strong> ${report.waste_type || "Tidak ditentukan"
                        }</p>
                          <p class="mb-1"><strong>Pelapor:</strong> ${report.user_name
                        }</p>
                          <p class="mb-0"><strong>Tanggal:</strong> ${new Date(
                            report.created_at
                        ).toLocaleDateString("id-ID", {
                            day: "numeric",
                            month: "short",
                            year: "numeric",
                        })}</p>
                      </div>
                  `);

                markerClusterGroup.addLayer(marker);
                markers.push(marker);

                const reportDate = new Date(report.created_at);
                if (!latestDate || reportDate > latestDate) {
                    latestDate = reportDate;
                    latestReport = report;
                }
            });

            if (latestReport) {
                map.setView(
                    [latestReport.latitude, latestReport.longitude],
                    20
                );
                console.log(
                    "Map centered to latest report:",
                    latestReport.latitude,
                    latestReport.longitude
                );
            }
        })
        .fail(function () {
            console.error("Gagal memuat data untuk peta");
            showAlert("error", "Gagal memuat data peta");
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
                    <div class="col-md-6">
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
                            <strong>Pelapor:</strong>
                            ${data.user.name}
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
                        ${data.image_path
                    ? `
                        <div class="mt-3">
                            <strong>Foto Sampah:</strong>
                            <img src="/storage/${data.image_path}" class="img-fluid rounded mt-2" alt="Foto Sampah" style="max-height: 200px;">
                        </div>
                        `
                    : ""
                }
                        ${data.feedback
                    ? `
                            <div class="mt-3">
                                <strong>Feedback Admin:</strong>
                                <div class="alert alert-success">
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
                    <div class="col-md-6">
                        <h6><i class="fas fa-map-marker-alt mr-2"></i>Lokasi Sampah</h6>
                        <div id="reportDetailMap" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    </div>
                </div>
            `;

            $("#reportDetailContent").html(content);
            $("#reportDetailModal").modal("show");

            // Initialize map after modal is shown
            setTimeout(() => {
                initReportDetailMap(
                    data.latitude,
                    data.longitude,
                    data.address || "Lokasi Sampah"
                );
            }, 500);
        })
        .fail(function () {
            showAlert("error", "Gagal memuat detail laporan");
        });
}

// Initialize report detail map
function initReportDetailMap(latitude, longitude, address) {
    if (reportDetailMap) {
        reportDetailMap.remove();
    }

    reportDetailMap = L.map("reportDetailMap").setView(
        [latitude, longitude],
        20
    );
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(reportDetailMap);

    // Add marker
    reportDetailMarker = L.marker([latitude, longitude]).addTo(reportDetailMap)
        .bindPopup(`
            <div style="min-width: 150px;">
                <h6><i class="fas fa-trash mr-1"></i>Lokasi Sampah</h6>
                <p class="mb-1"><strong>Alamat:</strong></p>
                <p class="mb-0">${address}</p>
                <p class="mb-0"><strong>Koordinat:</strong></p>
                <p class="mb-0">${latitude}, ${longitude}</p>
            </div>
        `);

    // Open popup automatically
    reportDetailMarker.openPopup();
}

// Update statistics
function updateStatistics() {
    $.get("/api/admin/statistics")
        .done(function (data) {
            $("#total-users").text(data.total_users);
            $("#total-reports").text(data.total_reports);
            $("#pending-reports").text(data.pending_reports);
            $("#completed-reports").text(data.completed_reports);
        })
        .fail(function () {
            console.error("Gagal memuat statistik");
        });
}

// Open user management modal
function openUserManagementModal() {
    $("#userManagementModal").modal("show");
    loadUsers();
}

// Load users with pagination and filters
function loadUsers() {
    const params = {
        page: currentUserPage,
        per_page: userPerPage,
        search: userFilters.search,
        role: userFilters.role,
        status: userFilters.status,
    };

    $.get("/api/admin/users", params)
        .done(function (data) {
            let tableBody = "";
            data.data.forEach(function (user) {
                tableBody += `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td><span class="badge badge-${user.roles[0]?.name === "admin"
                        ? "primary"
                        : "success"
                    }">${user.roles[0]?.name || "user"}</span></td>
                        <td><span class="badge badge-${user.is_active ? "success" : "danger"
                    }">${user.is_active ? "Aktif" : "Nonaktif"}</span></td>
                        <td>
                            <button class="btn btn-info btn-sm mr-1" onclick="editUser(${user.id
                    })">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-warning btn-sm mr-1" onclick="toggleUserStatus(${user.id
                    })">
                                <i class="fas fa-${user.is_active ? "ban" : "check"
                    }"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id
                    })">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $("#usersTable tbody").html(tableBody);

            // Update pagination info
            totalUsers = data.total;
            updateUserPagination();
        })
        .fail(function () {
            showAlert("error", "Gagal memuat data user");
        });
}

// Update user pagination
function updateUserPagination() {
    const totalPages = Math.ceil(totalUsers / userPerPage);
    const start = (currentUserPage - 1) * userPerPage + 1;
    const end = Math.min(currentUserPage * userPerPage, totalUsers);

    $("#userPaginationInfo").text(
        `Menampilkan ${start}-${end} dari ${totalUsers} data`
    );
    $("#userCurrentPage").text(currentUserPage);

    $("#userPrevPage").prop("disabled", currentUserPage === 1);
    $("#userNextPage").prop("disabled", currentUserPage === totalPages);
}

// Change user page
function changeUserPage(direction) {
    const newPage = currentUserPage + direction;
    if (newPage >= 1 && newPage <= Math.ceil(totalUsers / userPerPage)) {
        currentUserPage = newPage;
        loadUsers();
    }
}

// Function to request geolocation and start tracking
function requestGeolocation(manual = false) {
    console.log("Memulai tracking lokasi (manual: " + manual + ")");

    if (!navigator.geolocation) {
        console.warn("Geolocation tidak didukung oleh browser ini");
        if (manual) showAlert("warning", "Browser Anda tidak mendukung geolokasi");
        return;
    }

    if (manual) {
        showAlert("info", "Sedang mencari lokasi Anda...");
    }

    const map = window.dashboardMap || dashboardMap;
    if (!map) {
        console.error("Map instance tidak ditemukan");
        return;
    }

    // Stop existing watch if any
    if (window.watchID !== null) {
        navigator.geolocation.clearWatch(window.watchID);
        window.watchID = null;
    }

    window.watchID = navigator.geolocation.watchPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            console.log("Tracking lokasi update: ", lat, lng);

            // Update marker position
            if (window.currentLocationMarker) {
                window.currentLocationMarker.setLatLng([lat, lng]);
            } else {
                try {
                    window.currentLocationMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'current-location-marker',
                            html: '<div class="location-pulse" style="background: #4285F4; width: 22px; height: 22px; border-radius: 50%; border: 4px solid white; box-shadow: 0 0 20px rgba(66, 133, 244, 0.8);"></div>',
                            iconSize: [26, 26],
                            iconAnchor: [13, 13]
                        }),
                        zIndexOffset: 1000
                    }).addTo(map).bindPopup("<b>Posisi Anda Sekarang</b>");
                } catch (e) {
                    console.error("Gagal menambahkan custom marker, menggunakan marker standar: ", e);
                    window.currentLocationMarker = L.marker([lat, lng]).addTo(map).bindPopup("Posisi Anda");
                }
            }

            // Only auto-center if it's the first time or manual click
            if (manual || !window.isGeolocated) {
                map.setView([lat, lng], 18);
                if (manual) {
                    window.currentLocationMarker.openPopup();
                    showAlert("success", "Lokasi ditemukan!");
                    manual = false; // Reset so subsequent updates don't keep popping up alerts
                }
            }

            window.isGeolocated = true;

            // Update distances in all map popups
            if (typeof dashboardMarkers !== 'undefined' && dashboardMarkers.length > 0) {
                dashboardMarkers.forEach(function (marker) {
                    const reportLoc = marker.getLatLng();
                    const dist = L.latLng(lat, lng).distanceTo(reportLoc);
                    const dStr = dist > 1000 ? (dist / 1000).toFixed(2) + " km" : Math.round(dist) + " m";

                    const popup = marker.getPopup();
                    if (popup) {
                        const content = popup.getContent();
                        if (typeof content === 'string') {
                            const newContent = content.replace(/<strong>Jarak:<\/strong> <span class="distance-value">.*?<\/span>/, `<strong>Jarak:</strong> <span class="distance-value">${dStr}</span>`);
                            if (newContent !== content) {
                                marker.setPopupContent(newContent);
                            }
                        }
                    }
                });
            }

            // Proactively update any active route if needed
            if (window.routingControl) {
                const waypoints = window.routingControl.getWaypoints();
                if (waypoints && waypoints.length > 0) {
                    // Update the first waypoint (start point) to new location
                    window.routingControl.spliceWaypoints(0, 1, L.latLng(lat, lng));
                }
            }
        },
        function (error) {
            console.error("Tracking error code: " + error.code + ", message: " + error.message);
            if (manual) {
                let msg = "Gagal mendapatkan lokasi: " + error.message;
                if (error.code === 1) msg = "Izin lokasi ditolak. Silakan aktifkan di pengaturan browser.";
                showAlert("error", msg);
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 0
        }
    );
}
// Open add user modal
function openAddUserModal() {
    $("#addUserForm")[0].reset();
    $("#addUserModal").modal("show");
}

// Save user
function saveUser() {
    const formData = new FormData($("#addUserForm")[0]);

    $.ajax({
        url: "/api/admin/users",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                $("#addUserModal").modal("hide");
                loadUsers();
                updateStatistics();
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = "Terjadi kesalahan:\n";
                Object.keys(errors).forEach((key) => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                showAlert("error", errorMessage);
            } else {
                showAlert("error", "Gagal menambah user");
            }
        },
    });
}

// Edit user
function editUser(userId) {
    $.get(`/api/admin/users/${userId}`)
        .done(function (data) {
            $("#editUserId").val(data.id);
            $("#editUserName").val(data.name);
            $("#editUserUsername").val(data.username);
            $("#editUserEmail").val(data.email);
            $("#editUserPassword").val("");
            $("#editUserRole").val(data.roles[0]?.name || "user");
            $("#editUserModal").modal("show");
        })
        .fail(function () {
            showAlert("error", "Gagal memuat data user");
        });
}

// Update user
function updateUser() {
    const formData = new FormData($("#editUserForm")[0]);

    $.ajax({
        url: `/api/admin/users/${$("#editUserId").val()}`,
        type: "PUT",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                $("#editUserModal").modal("hide");
                loadUsers();
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = "Terjadi kesalahan:\n";
                Object.keys(errors).forEach((key) => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                showAlert("error", errorMessage);
            } else {
                showAlert("error", "Gagal mengupdate user");
            }
        },
    });
}

// Delete user
function deleteUser(userId) {
    if (confirm("Apakah Anda yakin ingin menghapus user ini?")) {
        $.ajax({
            url: `/api/admin/users/${userId}`,
            type: "DELETE",
            success: function (response) {
                if (response.success) {
                    showAlert("success", response.message);
                    loadUsers();
                    updateStatistics();
                }
            },
            error: function () {
                showAlert("error", "Gagal menghapus user");
            },
        });
    }
}

// Toggle user status
function toggleUserStatus(userId) {
    $.ajax({
        url: `/api/admin/users/${userId}/toggle-status`,
        type: "PUT",
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                loadUsers();
                updateStatistics();
            }
        },
        error: function () {
            showAlert("error", "Gagal mengubah status user");
        },
    });
}

// Open waste type modal
function openWasteTypeModal() {
    $("#wasteTypeModal").modal("show");
    loadWasteTypes();
}

// Load waste types
function loadWasteTypes() {
    $.get("/api/admin/waste-types")
        .done(function (data) {
            let tableBody = "";
            data.forEach(function (type) {
                tableBody += `
                    <tr>
                        <td>${type.name}</td>
                        <td><i class="${type.icon}" style="color: ${type.color
                    };"></i></td>
                        <td><span style="background-color: ${type.color
                    }; width: 20px; height: 20px; display: inline-block; border-radius: 3px;"></span></td>
                        <td><span class="badge badge-${type.is_active ? "success" : "danger"
                    }">${type.is_active ? "Aktif" : "Nonaktif"}</span></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="toggleWasteTypeStatus(${type.id
                    })">
                                <i class="fas fa-${type.is_active ? "ban" : "check"
                    }"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $("#wasteTypesTable tbody").html(tableBody);
        })
        .fail(function () {
            showAlert("error", "Gagal memuat data jenis sampah");
        });
}

// Toggle waste type status
function toggleWasteTypeStatus(typeId) {
    $.ajax({
        url: `/api/admin/waste-types/${typeId}/toggle-status`,
        type: "PUT",
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                loadWasteTypes();
            }
        },
        error: function () {
            showAlert("error", "Gagal mengubah status jenis sampah");
        },
    });
}

// Load waste types for filter
function loadWasteTypesForFilter() {
    $.get("/api/admin/waste-types").done(function (data) {
        let options = '<option value="">Semua Jenis</option>';
        data.forEach(function (type) {
            options += `<option value="${type.id}">${type.name}</option>`;
        });
        $("#reportTypeFilter").html(options);
    });
}

// Apply report filters
function applyReportFilters() {
    currentReportPage = 1;
    loadAllReports();
}

// Print reports
function printReports() {
    const params = new URLSearchParams({
        search: reportFilters.search || "",
        status: reportFilters.status || "",
        type: reportFilters.type || "",
        date_start: reportFilters.dateStart || "",
        date_end: reportFilters.dateEnd || "",
    });

    const printUrl = `/admin/reports/print?${params.toString()}`;
    window.open(printUrl, "_blank");
}

// ==================== DISTRICT MANAGEMENT FUNCTIONS ====================

// Open district management modal
function openDistrictManagementModal() {
    $("#districtManagementModal").modal("show");
    loadAllDistricts();
}

// Load all districts with pagination and filters
function loadAllDistricts() {
    const params = {
        page: currentDistrictPage,
        per_page: districtFilters.perPage,
        search: districtFilters.search,
        status: districtFilters.status,
    };

    $.get("/api/admin/districts", params)
        .done(function (data) {
            console.log("Districts data received:", data);
            renderDistrictsTable(data.data);
            renderDistrictsPagination(data);
        })
        .fail(function (xhr) {
            console.error("Error loading districts:", xhr);
            showAlert("error", "Gagal memuat data distrik");
        });
}

// Render districts table
function renderDistrictsTable(districts) {
    let table = "";
    districts.forEach((district, index) => {
        const startIndex =
            (currentDistrictPage - 1) * districtFilters.perPage + index + 1;
        table += `
            <tr>
                <td>${startIndex}</td>
                <td>
                    <strong>${district.name}</strong>
                    <br>
                    <small class="text-muted">${district.description || "Tidak ada deskripsi"
            }</small>
                </td>
                <td>${district.description || "-"}</td>
                <td>
                    <span class="badge badge-${district.is_active ? "success" : "secondary"
            }">
                        ${district.is_active ? "Aktif" : "Tidak Aktif"}
                    </span>
                </td>
                <td>
                    <span class="badge badge-info">${district.waste_reports_count || 0
            } laporan</span>
                </td>
                <td>
                    <button class="btn btn-info btn-sm mr-1" onclick="viewDistrictDetails(${district.id
            })">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-warning btn-sm mr-1" onclick="editDistrict(${district.id
            })">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-${district.is_active ? "secondary" : "success"
            } btn-sm mr-1" onclick="toggleDistrictStatus(${district.id
            })">
                        <i class="fas fa-${district.is_active ? "pause" : "play"
            }"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteDistrict(${district.id
            })">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $("#districtsTableBody").html(table);
}

// Render districts pagination
function renderDistrictsPagination(data) {
    let pagination = "";
    const currentPage = data.current_page;
    const lastPage = data.last_page;

    // Previous button
    if (currentPage > 1) {
        pagination += `<li class="page-item"><a class="page-link" href="#" onclick="changeDistrictPage(${currentPage - 1
            })">Previous</a></li>`;
    }

    // Page numbers
    for (let i = 1; i <= lastPage; i++) {
        if (i === currentPage) {
            pagination += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            pagination += `<li class="page-item"><a class="page-link" href="#" onclick="changeDistrictPage(${i})">${i}</a></li>`;
        }
    }

    // Next button
    if (currentPage < lastPage) {
        pagination += `<li class="page-item"><a class="page-link" href="#" onclick="changeDistrictPage(${currentPage + 1
            })">Next</a></li>`;
    }

    $("#districtsPagination").html(pagination);
}

// Change district page
function changeDistrictPage(page) {
    currentDistrictPage = page;
    loadAllDistricts();
}

// Open add district modal
function openAddDistrictModal() {
    $("#addDistrictModalLabel").html(
        '<i class="fas fa-plus mr-2"></i>Tambah Distrik'
    );
    $("#addDistrictForm")[0].reset();
    $("#districtId").val("");
    $("#districtColor").val("#3b82f6");
    $("#districtBoundaries").val("");

    // Clear previous polygon and markers
    districtPolygonPoints = [];
    districtMarkers = [];
    if (districtPolygon) {
        districtPolygon = null;
    }

    // Reset UI
    updatePolygonUI();

    $("#addDistrictModal").modal("show");

    // Initialize map after modal is shown
    $("#addDistrictModal").on("shown.bs.modal", function () {
        initializeDistrictMap();
        // Load and display existing districts
        loadExistingDistrictsOnMap();
        // Remove the event listener to prevent multiple initializations
        $(this).off("shown.bs.modal");
    });

    // Clean up map when modal is hidden
    $("#addDistrictModal").on("hidden.bs.modal", function () {
        if (districtMap) {
            // Remove all markers
            districtMarkers.forEach(function (marker) {
                districtMap.removeLayer(marker);
            });
            districtMarkers = [];

            // Remove existing district layers
            existingDistrictLayers.forEach(function (layer) {
                districtMap.removeLayer(layer);
            });
            existingDistrictLayers = [];

            if (districtPolygon) {
                districtMap.removeLayer(districtPolygon);
                districtPolygon = null;
            }

            districtMap.remove();
            districtMap = null;
        }
        districtPolygonPoints = [];
        // Remove event listeners
        $(this).off("shown.bs.modal hidden.bs.modal");
    });
}

// Initialize district map
function initializeDistrictMap() {
    // Remove existing map if any
    if (districtMap) {
        districtMap.remove();
        districtMap = null;
    }

    // Small delay to ensure container is fully visible
    setTimeout(function () {
        districtMap = L.map("districtMap", {
            preferCanvas: false
        }).setView([-6.2088, 106.8456], 13);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
            maxZoom: 19
        }).addTo(districtMap);

        // Force map to recalculate size after modal is shown
        setTimeout(function () {
            if (districtMap) {
                districtMap.invalidateSize();
            }
        }, 100);

        // Clear previous polygon and markers
        if (districtPolygon) {
            districtMap.removeLayer(districtPolygon);
            districtPolygon = null;
        }

        // Remove all markers
        districtMarkers.forEach(function (marker) {
            districtMap.removeLayer(marker);
        });
        districtMarkers = [];
        districtPolygonPoints = [];

        // Helper function to calculate distance between two points in meters
        function getDistance(lat1, lng1, lat2, lng2) {
            const R = 6371e3; // Earth radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c;
        }

        // Add click event to create polygon
        districtMap.on("click", function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Check if clicking on first point (within 30 meters) to close polygon
            if (districtPolygonPoints.length >= 3) {
                const firstPoint = districtPolygonPoints[0];
                const distance = getDistance(lat, lng, firstPoint[1], firstPoint[0]);

                if (distance < 30) {
                    // Close polygon by using first point
                    // Polygon is already closed, just update the display
                    showAlert("success", "Polygon ditutup! Klik 'Simpan' untuk menyimpan distrik.");
                    return;
                }
            }

            // Add new point
            districtPolygonPoints.push([lng, lat]);

            // Add marker for this point
            const currentColor = $("#districtColor").val() || "#3b82f6";
            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: currentColor,
                color: "#ffffff",
                weight: 2,
                fillOpacity: 1
            }).addTo(districtMap);

            // Add number label to marker
            marker.bindTooltip((districtPolygonPoints.length).toString(), {
                permanent: true,
                direction: "center",
                className: "marker-label"
            });

            districtMarkers.push(marker);

            // Remove previous polygon
            if (districtPolygon) {
                districtMap.removeLayer(districtPolygon);
            }

            // Create new polygon if we have at least 3 points
            if (districtPolygonPoints.length >= 3) {
                // Convert [lng, lat] to [lat, lng] for Leaflet
                const polygonCoords = districtPolygonPoints.map(function (point) {
                    return [point[1], point[0]]; // Convert [lng, lat] to [lat, lng]
                });

                districtPolygon = L.polygon(polygonCoords, {
                    color: currentColor,
                    fillColor: currentColor,
                    fillOpacity: 0.4,
                    weight: 3
                }).addTo(districtMap);
            }

            // Update boundaries input
            $("#districtBoundaries").val(JSON.stringify(districtPolygonPoints));

            // Update UI
            updatePolygonUI();

            // Show instruction
            if (districtPolygonPoints.length >= 3) {
                showAlert("info", "Klik titik pertama (titik #1) untuk menutup polygon, atau klik di tempat lain untuk menambahkan titik.");
            }
        });

        // Update polygon color when color picker changes
        $("#districtColor").off("change").on("change", function () {
            const newColor = $(this).val();

            // Update polygon
            if (districtPolygon && districtPolygonPoints.length >= 3) {
                districtMap.removeLayer(districtPolygon);
                // Convert [lng, lat] to [lat, lng] for Leaflet
                const polygonCoords = districtPolygonPoints.map(function (point) {
                    return [point[1], point[0]]; // Convert [lng, lat] to [lat, lng]
                });
                districtPolygon = L.polygon(polygonCoords, {
                    color: newColor,
                    fillColor: newColor,
                    fillOpacity: 0.4,
                    weight: 3
                }).addTo(districtMap);
            }

            // Update all markers
            districtMarkers.forEach(function (marker) {
                marker.setStyle({
                    fillColor: newColor,
                    color: "#ffffff"
                });
            });
        });
    }, 50);
}

// Update polygon UI (buttons and counter)
function updatePolygonUI() {
    const pointCount = districtPolygonPoints.length;
    $("#pointCounter").text("Titik: " + pointCount);

    // Enable/disable buttons
    if (pointCount > 0) {
        $("#btnRemoveLastPoint").prop("disabled", false);
        $("#btnResetPolygon").prop("disabled", false);
    } else {
        $("#btnRemoveLastPoint").prop("disabled", true);
        $("#btnResetPolygon").prop("disabled", true);
    }
}

// Remove last point from polygon
function removeLastPoint() {
    if (districtPolygonPoints.length === 0) {
        return;
    }

    // Remove last point
    districtPolygonPoints.pop();

    // Remove last marker
    if (districtMarkers.length > 0) {
        const lastMarker = districtMarkers.pop();
        districtMap.removeLayer(lastMarker);
    }

    // Remove polygon if exists
    if (districtPolygon) {
        districtMap.removeLayer(districtPolygon);
        districtPolygon = null;
    }

    // Recreate polygon if we still have 3+ points
    if (districtPolygonPoints.length >= 3) {
        const currentColor = $("#districtColor").val() || "#3b82f6";
        // Convert [lng, lat] to [lat, lng] for Leaflet
        const polygonCoords = districtPolygonPoints.map(function (point) {
            return [point[1], point[0]]; // Convert [lng, lat] to [lat, lng]
        });
        districtPolygon = L.polygon(polygonCoords, {
            color: currentColor,
            fillColor: currentColor,
            fillOpacity: 0.4,
            weight: 3
        }).addTo(districtMap);
    }

    // Update boundaries input
    $("#districtBoundaries").val(JSON.stringify(districtPolygonPoints));

    // Update UI
    updatePolygonUI();

    if (districtPolygonPoints.length === 0) {
        showAlert("info", "Semua titik telah dihapus. Klik pada peta untuk menambahkan titik baru.");
    }
}

// Load and display existing districts on map
function loadExistingDistrictsOnMap(excludeDistrictId = null) {
    if (!districtMap) {
        // Wait a bit for map to be ready
        setTimeout(function () {
            loadExistingDistrictsOnMap(excludeDistrictId);
        }, 200);
        return;
    }

    // Remove existing district layers first
    existingDistrictLayers.forEach(function (layer) {
        districtMap.removeLayer(layer);
    });
    existingDistrictLayers = [];

    // Load all districts
    $.get("/api/admin/districts", { per_page: 1000 })
        .done(function (response) {
            if (response.data && response.data.length > 0) {
                response.data.forEach(function (district) {
                    // Skip current district being edited
                    if (excludeDistrictId && district.id == excludeDistrictId) {
                        return;
                    }

                    // Skip if no boundaries
                    if (!district.boundaries || district.boundaries.length < 3) {
                        return;
                    }

                    // Convert [lng, lat] to [lat, lng] for Leaflet
                    const polygonCoords = district.boundaries.map(function (point) {
                        return [point[1], point[0]];
                    });

                    // Create polygon with semi-transparent style
                    const districtLayer = L.polygon(polygonCoords, {
                        color: district.color || "#666666",
                        fillColor: district.color || "#666666",
                        fillOpacity: 0.2,
                        weight: 2,
                        opacity: 0.6
                    }).addTo(districtMap);

                    // Add tooltip with district name
                    districtLayer.bindTooltip(district.name, {
                        permanent: false,
                        direction: "center",
                        className: "existing-district-tooltip"
                    });

                    existingDistrictLayers.push(districtLayer);
                });
            }
        })
        .fail(function (xhr) {
            console.error("Error loading existing districts:", xhr);
        });
}

// Reset polygon completely
function resetPolygon() {
    if (confirm("Apakah Anda yakin ingin menghapus semua titik polygon?")) {
        // Remove all markers
        districtMarkers.forEach(function (marker) {
            districtMap.removeLayer(marker);
        });
        districtMarkers = [];

        // Remove polygon
        if (districtPolygon) {
            districtMap.removeLayer(districtPolygon);
            districtPolygon = null;
        }

        // Clear points
        districtPolygonPoints = [];

        // Update boundaries input
        $("#districtBoundaries").val("");

        // Update UI
        updatePolygonUI();

        showAlert("info", "Polygon telah direset. Klik pada peta untuk membuat polygon baru.");
    }
}

// Edit district
function editDistrict(id) {
    $.get(`/api/admin/districts/${id}`)
        .done(function (data) {
            $("#addDistrictModalLabel").html(
                '<i class="fas fa-edit mr-2"></i>Edit Distrik'
            );
            $("#districtId").val(data.id);
            $("#districtName").val(data.name);
            $("#districtDescription").val(data.description);
            $("#districtColor").val(data.color);
            $("#districtBoundaries").val(JSON.stringify(data.boundaries));

            // Clear previous polygon and markers
            districtPolygonPoints = [];
            districtMarkers = [];
            if (districtPolygon) {
                districtPolygon = null;
            }

            // Reset UI
            updatePolygonUI();

            // Store boundaries for later use
            const existingBoundaries = data.boundaries || [];
            const existingColor = data.color || "#3b82f6";

            $("#addDistrictModal").modal("show");

            // Initialize map after modal is shown
            $("#addDistrictModal").on("shown.bs.modal", function () {
                initializeDistrictMap();
                // Load and display existing districts (except current one being edited)
                loadExistingDistrictsOnMap(data.id);

                // Add existing polygon to map after initialization
                setTimeout(function () {
                    if (existingBoundaries.length > 0 && districtMap) {
                        districtPolygonPoints = existingBoundaries;

                        // Add markers for each point
                        existingBoundaries.forEach(function (point, index) {
                            const marker = L.circleMarker([point[1], point[0]], {
                                radius: 8,
                                fillColor: existingColor,
                                color: "#ffffff",
                                weight: 2,
                                fillOpacity: 1
                            }).addTo(districtMap);

                            marker.bindTooltip((index + 1).toString(), {
                                permanent: true,
                                direction: "center",
                                className: "marker-label"
                            });

                            districtMarkers.push(marker);
                        });

                        // Convert [lng, lat] to [lat, lng] for Leaflet
                        const polygonCoords = existingBoundaries.map(function (point) {
                            return [point[1], point[0]]; // Convert [lng, lat] to [lat, lng]
                        });

                        districtPolygon = L.polygon(polygonCoords, {
                            color: existingColor,
                            fillColor: existingColor,
                            fillOpacity: 0.4,
                            weight: 3
                        }).addTo(districtMap);

                        // Update UI
                        updatePolygonUI();

                        // Fit map to polygon bounds
                        if (districtPolygon) {
                            districtMap.fitBounds(districtPolygon.getBounds());
                        }
                    }
                }, 200);

                // Remove the event listener to prevent multiple initializations
                $(this).off("shown.bs.modal");
            });

            // Clean up map when modal is hidden
            $("#addDistrictModal").on("hidden.bs.modal", function () {
                if (districtMap) {
                    // Remove all markers
                    districtMarkers.forEach(function (marker) {
                        districtMap.removeLayer(marker);
                    });
                    districtMarkers = [];

                    if (districtPolygon) {
                        districtMap.removeLayer(districtPolygon);
                        districtPolygon = null;
                    }

                    districtMap.remove();
                    districtMap = null;
                }
                districtPolygonPoints = [];
                // Remove event listeners
                $(this).off("shown.bs.modal hidden.bs.modal");
            });
        })
        .fail(function () {
            showAlert("error", "Gagal memuat data distrik");
        });
}

// Submit district
function submitDistrict() {
    const formData = {
        name: $("#districtName").val(),
        description: $("#districtDescription").val(),
        color: $("#districtColor").val(),
        boundaries: JSON.parse($("#districtBoundaries").val() || "[]"),
    };

    const districtId = $("#districtId").val();
    const url = districtId
        ? `/api/admin/districts/${districtId}`
        : "/api/admin/districts";
    const method = districtId ? "PUT" : "POST";

    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                $("#addDistrictModal").modal("hide");
                loadAllDistricts();
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = "Terjadi kesalahan:\n";
                Object.keys(errors).forEach((key) => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                showAlert("error", errorMessage);
            } else {
                showAlert("error", "Gagal menyimpan distrik");
            }
        },
    });
}

// Toggle district status
function toggleDistrictStatus(id) {
    $.ajax({
        url: `/api/admin/districts/${id}/toggle-status`,
        type: "PUT",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                loadAllDistricts();
            }
        },
        error: function () {
            showAlert("error", "Gagal mengubah status distrik");
        },
    });
}

// Delete district
function deleteDistrict(id) {
    if (confirm("Apakah Anda yakin ingin menghapus distrik ini?")) {
        $.ajax({
            url: `/api/admin/districts/${id}`,
            type: "DELETE",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    showAlert("success", response.message);
                    loadAllDistricts();
                } else {
                    showAlert("error", response.message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    showAlert("error", response.message);
                } else {
                    showAlert("error", "Gagal menghapus distrik");
                }
            },
        });
    }
}

// View district details
function viewDistrictDetails(id) {
    $.get(`/api/admin/districts/${id}/statistics`)
        .done(function (response) {
            if (response.success) {
                const stats = response.data;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Laporan</h5>
                                    <h2 class="text-primary">${stats.total_reports}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Laporan Menunggu</h5>
                                    <h2 class="text-warning">${stats.pending_reports}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Laporan Diproses</h5>
                                    <h2 class="text-info">${stats.processed_reports}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Laporan Selesai</h5>
                                    <h2 class="text-success">${stats.completed_reports}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $("#districtStatisticsContent").html(content);
                $("#districtStatisticsModal").modal("show");
            }
        })
        .fail(function () {
            showAlert("error", "Gagal memuat statistik distrik");
        });
}

// Load all reports with pagination and filters
function loadAllReports() {
    const params = {
        page: currentReportPage,
        per_page: reportFilters.perPage,
        search: reportFilters.search,
        reporter: reportFilters.reporter,
        status: reportFilters.status,
        type: reportFilters.type,
        date_start: reportFilters.dateStart,
        date_end: reportFilters.dateEnd,
        feedback: reportFilters.feedback,
    };

    $.get("/api/admin/reports", params)
        .done(function (data) {
            console.log("Reports data received:", data);
            console.log("First report:", data.data[0]);

            let table = `
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pelapor</th>
                                <th>Judul</th>
                                <th>Jenis Sampah</th>
                                <th>Distrik</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.data.forEach(function (report) {
                table += `
                    <tr>
                        <td>${new Date(report.created_at).toLocaleDateString(
                    "id-ID",
                    { day: "numeric", month: "short", year: "numeric" }
                )}</td>
                        <td>${report.user.name}</td>
                        <td>${report.title || "Laporan Sampah"}</td>
                        <td>${report.waste_type
                        ? report.waste_type.name
                        : "Tidak ditentukan"
                    }</td>
                        <td>
                            ${report.district
                        ? `<span class="badge badge-info">${report.district.name}</span>`
                        : '<span class="badge badge-secondary">Luar Distrik</span>'
                    }
                        </td>
                        <td>
                            <span class="badge badge-${getStatusBadgeClass(
                        report.status
                    )}">${report.status_text}</span>
                            ${report.feedback
                        ? '<span class="badge badge-info ml-1"><i class="fas fa-comment-dots"></i> Ada Feedback</span>'
                        : ""
                    }
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm mr-1" onclick="viewReportDetail(${report.id
                    })">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-sm mr-1" onclick="openChangeStatusModal(${report.id
                    }, '${report.status}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${!report.feedback
                        ? `<button class="btn btn-success btn-sm" onclick="openAddFeedbackModal(${report.id})">
                                <i class="fas fa-comment-dots"></i>
                            </button>`
                        : ""
                    }
                        </td>
                    </tr>
                `;
            });

            table += `
                        </tbody>
                    </table>
                </div>
            `;

            $("#reportsTableContainer").html(table);

            // Update pagination info
            totalReports = data.total;
            updateReportPagination();
        })
        .fail(function () {
            showAlert("error", "Gagal memuat semua laporan");
        });
}

// Update report pagination
function updateReportPagination() {
    const totalPages = Math.ceil(totalReports / reportFilters.perPage);
    const start = (currentReportPage - 1) * reportFilters.perPage + 1;
    const end = Math.min(
        currentReportPage * reportFilters.perPage,
        totalReports
    );

    $("#reportPaginationInfo").text(
        `Menampilkan ${start}-${end} dari ${totalReports} data`
    );
    $("#reportCurrentPage").text(currentReportPage);

    $("#reportPrevPage").prop("disabled", currentReportPage === 1);
    $("#reportNextPage").prop("disabled", currentReportPage === totalPages);
}

// Change report page
function changeReportPage(direction) {
    const newPage = currentReportPage + direction;
    if (
        newPage >= 1 &&
        newPage <= Math.ceil(totalReports / reportFilters.perPage)
    ) {
        currentReportPage = newPage;
        loadAllReports();
    }
}

// Open change status modal
function openChangeStatusModal(reportId, currentStatus) {
    $("#statusReportId").val(reportId);
    $("#reportStatus").val(currentStatus);
    $("#adminNotes").val("");
    $("#changeStatusModal").modal("show");
}

// Update report status
function updateReportStatus() {
    const form = $("#changeStatusForm")[0];
    const formData = new FormData(form);

    // Debug: Log form data
    console.log("Form data:");
    for (let [key, value] of formData.entries()) {
        console.log(key + ": " + value);
    }

    $.ajax({
        url: `/api/admin/reports/${$("#statusReportId").val()}/update-status`,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            console.log("Success response:", response);
            if (response.success) {
                showAlert("success", response.message);
                $("#changeStatusModal").modal("hide");
                loadAllReports();
                updateStatistics();
            }
        },
        error: function (xhr) {
            console.log("Error response:", xhr.responseJSON);
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = "Terjadi kesalahan:\n";
                Object.keys(errors).forEach((key) => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                showAlert("error", errorMessage);
            } else {
                showAlert("error", "Gagal mengupdate status laporan");
            }
        },
    });
}

// Open add feedback modal
function openAddFeedbackModal(reportId) {
    $("#feedbackReportId").val(reportId);
    $("#feedbackText").val("");
    $("#feedbackImage").val("");
    $("#feedbackFilePreview").hide();
    $("#feedbackFileUploadArea").show();
    $("#addFeedbackModal").modal("show");
}

// Select feedback file
function selectFeedbackFile() {
    $("#feedbackImage").click();
}

// Handle feedback file selection
$("#feedbackImage").on("change", function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $("#feedbackImagePreview").attr("src", e.target.result);
            $("#feedbackFilePreview").show();
            $("#feedbackFileUploadArea").hide();
        };
        reader.readAsDataURL(file);
    }
});

// Remove feedback image
function removeFeedbackImage() {
    $("#feedbackImage").val("");
    $("#feedbackFilePreview").hide();
    $("#feedbackFileUploadArea").show();
}

// Submit feedback
function submitFeedback() {
    const form = $("#addFeedbackForm")[0];
    const formData = new FormData(form);

    $.ajax({
        url: `/api/admin/reports/${$("#feedbackReportId").val()}/feedback`,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                showAlert("success", response.message);
                $("#addFeedbackModal").modal("hide");
                loadAllReports();
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorMessage = "Terjadi kesalahan:\n";
                Object.keys(errors).forEach((key) => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                showAlert("error", errorMessage);
            } else {
                showAlert("error", "Gagal menambahkan feedback");
            }
        },
    });
}
