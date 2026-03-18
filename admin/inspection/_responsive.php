<style>
.inspection-page .inspection-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.inspection-page .inspection-toolbar > * {
    flex-shrink: 0;
}

.inspection-page .inspection-filter-form .form-select,
.inspection-page .inspection-filter-form .form-control,
.inspection-page .inspection-filter-form .btn {
    min-height: 44px;
}

.inspection-page .inspection-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.inspection-page .inspection-table .badge {
    white-space: normal;
}

.inspection-page .inspection-table-wrap {
    overflow-x: auto;
}

.inspection-page .inspection-table-wrap::-webkit-scrollbar {
    height: 8px;
}

.inspection-page .inspection-table-wrap::-webkit-scrollbar-thumb {
    background: rgba(44, 62, 80, 0.2);
    border-radius: 999px;
}

.inspection-page .inspection-modal .modal-dialog {
    max-width: min(1140px, calc(100% - 1rem));
}

.inspection-page .inspection-modal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.inspection-page .inspection-search-row {
    display: flex;
    gap: 8px;
}

.inspection-page .inspection-card-link {
    color: inherit;
}

.inspection-page .inspection-card-link:hover {
    color: inherit;
}

.inspection-page .inspection-table-mobile-note {
    display: none;
}

@media (max-width: 991px) {
    .inspection-page .page-header {
        display: block !important;
    }

    .inspection-page .page-header > div:last-child,
    .inspection-page .page-header > a:last-child {
        margin-top: 14px;
    }

    .inspection-page .inspection-toolbar > * {
        flex: 1 1 220px;
    }

    .inspection-page .content-card {
        padding: 18px;
    }

    .inspection-page .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        padding-bottom: 6px;
    }

    .inspection-page .nav-tabs .nav-item {
        flex: 0 0 auto;
    }
}

@media (max-width: 767px) {
    .inspection-page .admin-content {
        padding: 16px 12px;
    }

    .inspection-page .page-title {
        font-size: 1.7rem;
    }

    .inspection-page .inspection-toolbar {
        flex-direction: column;
    }

    .inspection-page .inspection-toolbar > * {
        width: 100%;
        flex: 1 1 auto;
    }

    .inspection-page .inspection-filter-form > [class*="col-"] {
        width: 100%;
    }

    .inspection-page .inspection-search-row {
        display: grid;
        grid-template-columns: 1fr auto;
    }

    .inspection-page .inspection-filter-form .btn,
    .inspection-page .inspection-toolbar .btn,
    .inspection-page .inspection-toolbar .dropdown,
    .inspection-page .inspection-toolbar .dropdown > .btn {
        width: 100%;
    }

    .inspection-page .inspection-table-mobile-note {
        display: block;
        color: var(--secondary);
        font-size: 0.8rem;
        margin-bottom: 10px;
    }

    .inspection-page .inspection-table thead {
        display: none;
    }

    .inspection-page .inspection-table,
    .inspection-page .inspection-table tbody,
    .inspection-page .inspection-table tr,
    .inspection-page .inspection-table td {
        display: block;
        width: 100%;
    }

    .inspection-page .inspection-table tr {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .inspection-page .inspection-table tbody td {
        border: 0;
        padding: 8px 0;
    }

    .inspection-page .inspection-table tbody td::before {
        content: attr(data-label);
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--secondary);
        margin-bottom: 4px;
    }

    .inspection-page .inspection-table tbody tr:hover {
        background: #fff;
    }

    .inspection-page .inspection-actions {
        justify-content: flex-start;
    }

    .inspection-page .inspection-actions .btn {
        min-width: 42px;
    }

    .inspection-page .inspection-modal .modal-dialog {
        margin: 0;
        max-width: 100%;
        min-height: 100vh;
    }

    .inspection-page .inspection-modal .modal-content {
        min-height: 100vh;
        border-radius: 0;
    }

    .inspection-page .inspection-modal .modal-body {
        max-height: none;
        overflow-y: visible;
    }

    .inspection-files-page .inspection-table-wrap {
        overflow-x: visible;
    }

    .inspection-files-page .inspection-table tbody tr {
        padding: 0;
        border: 0;
        box-shadow: none;
        margin-bottom: 14px;
        background: transparent;
    }

    .inspection-files-page .inspection-table tbody td {
        padding: 0;
    }

    .inspection-files-page .inspection-table tbody td::before {
        display: none;
    }

    .inspection-files-page .inspection-table tbody td:not(:first-child) {
        display: none;
    }

    .inspection-files-page .inspection-file-number-desktop {
        display: none;
    }

    .inspection-files-page .inspection-mobile-file-card {
        display: block;
        background: #fff;
        border: 1px solid #e6ecf3;
        border-radius: 16px;
        padding: 14px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .inspection-files-page .inspection-mobile-file-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .inspection-files-page .inspection-mobile-file-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .inspection-files-page .inspection-mobile-file-block + .inspection-mobile-file-block {
        margin-top: 10px;
    }

    .inspection-files-page .inspection-mobile-file-grid .inspection-mobile-file-block + .inspection-mobile-file-block {
        margin-top: 0;
    }

    .inspection-files-page .inspection-mobile-file-label {
        color: var(--secondary);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .inspection-files-page .inspection-mobile-file-value {
        color: #1f2937;
        font-size: 0.95rem;
        line-height: 1.45;
        word-break: break-word;
    }

    .inspection-files-page .inspection-mobile-file-value small {
        display: block;
        margin-top: 2px;
        color: var(--secondary);
        font-size: 0.82rem;
    }

    .inspection-files-page .inspection-mobile-actions-menu {
        position: relative;
        flex-shrink: 0;
    }

    .inspection-files-page .inspection-mobile-actions-toggle {
        list-style: none;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d7dfeb;
        border-radius: 10px;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
    }

    .inspection-files-page .inspection-mobile-actions-toggle::-webkit-details-marker {
        display: none;
    }

    .inspection-files-page .inspection-mobile-actions-menu[open] .inspection-mobile-actions-toggle {
        background: #eef4ff;
        border-color: #bfd2ff;
        color: #0d6efd;
    }

    .inspection-files-page .inspection-mobile-actions-list {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 5;
        display: flex;
        gap: 8px;
        padding: 8px;
        background: #fff;
        border: 1px solid #dde6f0;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
    }

    .inspection-files-page .inspection-mobile-actions-list .btn {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 10px;
        background: #f8fafc;
    }
}

@media (min-width: 768px) {
    .inspection-files-page .inspection-mobile-file-card {
        display: none;
    }
}
</style>
