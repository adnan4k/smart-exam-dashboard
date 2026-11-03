{{--
    Self-contained styles for the contest rankings and prize pages.

    Plain CSS under the `.pz` namespace on purpose: these pages use no Tailwind
    utilities and no Bootstrap/Soft UI classes (.card, .table, .row, .w-N ...),
    so they render the same whether or not public/css/app.css was rebuilt, and
    Soft UI's percentage widths, Open Sans font and table padding cannot reach
    them.
--}}
<style>
    .pz {
        --pz-brand: #58706D;
        --pz-brand-dark: #455956;
        --pz-brand-soft: #eef3f2;
        --pz-ink: #0f172a;
        --pz-text: #334155;
        --pz-muted: #64748b;
        --pz-faint: #94a3b8;
        --pz-line: #e2e8f0;
        --pz-line-soft: #f1f5f9;
        --pz-surface: #f8fafc;
        --pz-amber: #f59e0b;
        --pz-radius: 14px;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.45;
        color: var(--pz-text);
        padding: 0 4px 24px;
    }
    .pz *, .pz *::before, .pz *::after { box-sizing: border-box; }
    .pz p, .pz h2, .pz h3, .pz dl, .pz dd, .pz dt, .pz ul { margin: 0; padding: 0; font-size: inherit; line-height: inherit; font-weight: inherit; }
    .pz ul { list-style: none; }
    .pz a { color: var(--pz-brand); text-decoration: none; }
    .pz a:hover { text-decoration: underline; }
    .pz [x-cloak] { display: none !important; }
    .pz .pz-num { font-variant-numeric: tabular-nums; }
    .pz .pz-strong { color: var(--pz-ink); font-weight: 700; }
    .pz .pz-faint { color: var(--pz-faint); }
    .pz .pz-muted { color: var(--pz-muted); }

    /* Cards */
    .pz .pz-card {
        background: #fff;
        border: 1px solid var(--pz-line);
        border-radius: var(--pz-radius);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .pz .pz-card-head { padding: 20px 22px; border-bottom: 1px solid var(--pz-line-soft); }
    .pz .pz-card-body { padding: 20px 22px; }
    .pz .pz-head-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .pz .pz-title { font-size: 17px; font-weight: 700; color: var(--pz-ink); letter-spacing: -.01em; }
    .pz .pz-sub { font-size: 12.5px; color: var(--pz-faint); margin-top: 3px; }
    .pz .pz-back { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; margin: 0 0 12px; }

    /* Stat tiles */
    .pz .pz-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 18px; }
    .pz .pz-stat { display: flex; align-items: center; gap: 12px; padding: 14px; background: var(--pz-surface); border: 1px solid var(--pz-line); border-radius: 12px; min-width: 0; }
    .pz .pz-stat-icon { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
    .pz .pz-stat-icon.is-blue  { background: #eff6ff; color: #2563eb; }
    .pz .pz-stat-icon.is-brand { background: var(--pz-brand-soft); color: var(--pz-brand); }
    .pz .pz-stat-icon.is-amber { background: #fffbeb; color: #d97706; }
    .pz .pz-stat-icon.is-slate { background: var(--pz-line-soft); color: var(--pz-muted); }
    .pz .pz-stat-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--pz-faint); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pz .pz-stat-value { font-size: 19px; font-weight: 800; color: var(--pz-ink); font-variant-numeric: tabular-nums; margin-top: 2px; white-space: nowrap; }

    /* Filters and form controls */
    .pz .pz-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--pz-line-soft); }
    .pz .pz-filters .pz-search { flex: 1 1 260px; max-width: 420px; }
    .pz .pz-filters .pz-select { flex: 0 1 200px; width: auto; }
    .pz .pz-hint { font-size: 11.5px; color: var(--pz-faint); margin-top: 10px; }
    .pz .pz-input, .pz .pz-select, .pz .pz-textarea {
        display: block; width: 100%; height: 38px; padding: 0 12px;
        font: inherit; font-size: 13px; color: var(--pz-ink);
        background: #fff; border: 1px solid var(--pz-line); border-radius: 10px;
        box-shadow: none; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .pz .pz-textarea { height: auto; min-height: 76px; padding: 10px 12px; resize: vertical; }
    .pz .pz-select { padding-right: 32px; appearance: none; -webkit-appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; }
    .pz .pz-input:focus, .pz .pz-select:focus, .pz .pz-textarea:focus { border-color: var(--pz-brand); box-shadow: 0 0 0 3px rgba(88, 112, 109, .15); }
    .pz .pz-input::placeholder, .pz .pz-textarea::placeholder { color: var(--pz-faint); }
    .pz .pz-search { position: relative; }
    .pz .pz-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--pz-faint); pointer-events: none; }
    .pz .pz-search .pz-input { padding-left: 34px; }

    /* Buttons */
    .pz .pz-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        height: 36px; padding: 0 14px; border-radius: 10px; border: 1px solid transparent;
        font: inherit; font-size: 13px; font-weight: 600; line-height: 1; white-space: nowrap;
        cursor: pointer; text-decoration: none !important; transition: background .15s, color .15s, border-color .15s;
    }
    .pz .pz-btn i { font-size: 11px; }
    .pz .pz-btn-primary { background: var(--pz-brand); color: #fff; }
    .pz .pz-btn-primary:hover { background: var(--pz-brand-dark); color: #fff; }
    .pz .pz-btn-outline { background: #fff; color: var(--pz-brand); border-color: var(--pz-brand); }
    .pz .pz-btn-outline:hover { background: var(--pz-brand-soft); }
    .pz .pz-btn-quiet { background: #fff; color: var(--pz-muted); border-color: var(--pz-line); }
    .pz .pz-btn-quiet:hover { color: var(--pz-ink); background: var(--pz-surface); }
    .pz .pz-btn-sm { height: 30px; padding: 0 11px; font-size: 12px; border-radius: 8px; }
    .pz .pz-btn-icon { width: 30px; padding: 0; }
    .pz .pz-btn-danger:hover { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }

    /* Tables */
    .pz .pz-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .pz .pz-table { width: 100%; border-collapse: collapse; }
    .pz .pz-table th {
        padding: 11px 14px; text-align: left; white-space: nowrap;
        font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--pz-muted);
        background: var(--pz-surface); border-bottom: 1px solid var(--pz-line);
    }
    .pz .pz-table td { padding: 13px 14px; font-size: 13px; color: var(--pz-text); border-bottom: 1px solid var(--pz-line-soft); vertical-align: middle; }
    .pz .pz-table .pz-c { text-align: center; }
    .pz .pz-table .pz-r { text-align: right; }
    .pz .pz-table .pz-nowrap { white-space: nowrap; }
    .pz .pz-table tbody.pz-group:hover > tr:first-child { background: #fbfcfd; }
    .pz .pz-clickable { cursor: pointer; }
    .pz .pz-chevron { color: var(--pz-faint); font-size: 11px; transition: transform .2s; display: inline-block; }
    .pz .pz-chevron.is-open { transform: rotate(180deg); }

    /* Rank badge, avatar, person */
    .pz .pz-rank { display: inline-flex; align-items: center; justify-content: center; gap: 3px; min-width: 30px; height: 30px; padding: 0 8px; border-radius: 999px; font-size: 12px; font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pz-muted); }
    .pz .pz-rank.r1 { background: #fef3c7; color: #92400e; }
    .pz .pz-rank.r1 i { color: #d97706; font-size: 10px; }
    .pz .pz-rank.r2 { background: #e2e8f0; color: #334155; }
    .pz .pz-rank.r3 { background: #fff7ed; color: #9a3412; box-shadow: inset 0 0 0 1px #fed7aa; }
    .pz .pz-person { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .pz .pz-avatar { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; background: var(--pz-brand-soft); color: var(--pz-brand); font-size: 12px; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; justify-content: center; }
    .pz .pz-avatar.is-lg { width: 44px; height: 44px; flex-basis: 44px; font-size: 14px; }
    .pz .pz-person-text { min-width: 0; max-width: 240px; }
    .pz .pz-name { font-size: 13px; font-weight: 700; color: var(--pz-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pz .pz-meta { font-size: 11.5px; color: var(--pz-faint); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Pills */
    .pz .pz-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 600; white-space: nowrap; border: 1px solid transparent; }
    .pz .pz-pill.is-brand { background: var(--pz-brand-soft); color: #3f5653; }
    .pz .pz-pill.is-amber { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .pz .pz-pill.is-green { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .pz .pz-pill.is-muted { background: var(--pz-line-soft); color: var(--pz-muted); }
    .pz .pz-pill.is-sm { font-size: 10.5px; padding: 1px 7px; }
    .pz .pz-stars { color: #b45309; font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .pz .pz-stars i { color: var(--pz-amber); font-size: 11px; }

    /* Expanded row */
    .pz .pz-detail > td { background: var(--pz-surface); padding: 18px 22px 20px; }
    .pz .pz-detail-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; margin-bottom: 18px; }
    .pz .pz-label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--pz-faint); margin-bottom: 8px; }
    .pz .pz-contact { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px 24px; }
    .pz .pz-contact dt { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--pz-faint); margin-bottom: 2px; }
    .pz .pz-contact dd { font-size: 13px; font-weight: 600; color: var(--pz-ink); overflow-wrap: anywhere; }
    .pz .pz-list li { font-size: 12.5px; padding: 7px 0; border-bottom: 1px dashed var(--pz-line); }
    .pz .pz-list li:last-child { border-bottom: 0; }
    .pz .pz-mini { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid var(--pz-line); border-radius: 10px; overflow: hidden; }
    .pz .pz-mini th { padding: 8px 12px; text-align: left; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--pz-faint); border-bottom: 1px solid var(--pz-line); white-space: nowrap; }
    .pz .pz-mini td { padding: 9px 12px; font-size: 12.5px; border-bottom: 1px solid var(--pz-line-soft); }
    .pz .pz-mini tr:last-child td { border-bottom: 0; }

    /* Pagination, empty, flash */
    .pz .pz-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 22px; border-top: 1px solid var(--pz-line-soft); font-size: 12.5px; color: var(--pz-muted); }
    .pz .pz-pager-links { display: flex; gap: 6px; }
    .pz .pz-empty { padding: 48px 20px; text-align: center; }
    .pz .pz-empty i { font-size: 26px; color: var(--pz-faint); margin-bottom: 10px; display: block; }
    .pz .pz-empty .pz-title { font-size: 15px; }
    .pz .pz-flash { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 14px; border: 1px solid; }
    .pz .pz-flash.is-good { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .pz .pz-flash.is-bad  { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    .pz .pz-flash ul { list-style: disc; padding-left: 18px; font-weight: 500; }

    /* Grant form */
    .pz .pz-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 3fr); gap: 16px; align-items: start; }
    .pz .pz-stack > * + * { margin-top: 16px; }
    .pz .pz-stack .pz-card { margin-bottom: 0; }
    .pz .pz-fields > * + * { margin-top: 16px; }
    .pz .pz-row { display: grid; gap: 12px; }
    .pz .pz-row.is-2-1 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .pz .pz-row.is-1-1 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pz .pz-field-label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .pz .pz-field-help { font-size: 11.5px; color: var(--pz-faint); margin-top: 6px; }
    .pz .pz-choice { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .pz .pz-choice-option { position: relative; display: flex; align-items: center; gap: 10px; padding: 12px; border: 2px solid var(--pz-line); border-radius: 12px; cursor: pointer; background: #fff; transition: border-color .15s, background .15s; margin: 0; }
    .pz .pz-choice-option:hover { background: var(--pz-surface); }
    .pz .pz-choice-option.is-on { border-color: var(--pz-brand); background: #f3f7f6; }
    .pz .pz-choice-option input { position: absolute; opacity: 0; pointer-events: none; }
    .pz .pz-choice-icon { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; background: var(--pz-line-soft); color: var(--pz-muted); font-size: 13px; transition: background .15s, color .15s; }
    .pz .pz-choice-option.is-on .pz-choice-icon { background: var(--pz-brand); color: #fff; }
    .pz .pz-choice-title { font-size: 13px; font-weight: 700; color: var(--pz-ink); }
    .pz .pz-choice-desc { font-size: 11.5px; color: var(--pz-faint); }
    .pz .pz-choice-check { margin-left: auto; color: var(--pz-brand); }
    .pz .pz-check { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--pz-text); cursor: pointer; margin-bottom: 0; }
    .pz .pz-check input { width: 16px; height: 16px; margin: 0; accent-color: var(--pz-brand); cursor: pointer; }
    .pz .pz-actions { display: flex; justify-content: flex-end; gap: 8px; padding-top: 16px; margin-top: 4px; border-top: 1px solid var(--pz-line-soft); }
    /* Alpine's x-show clears an inline display, so layout lives on an inner element. */
    .pz .pz-btn-row { display: inline-flex; align-items: center; gap: 6px; }
    .pz .pz-inline-form { display: grid; gap: 6px; min-width: 170px; }
    .pz .pz-inline-form .pz-input, .pz .pz-inline-form .pz-select { height: 32px; font-size: 12px; }

    /* Responsive */
    @media (max-width: 1365px) { .pz .pz-hide-lg { display: none; } }
    @media (max-width: 1023px) {
        .pz .pz-layout { grid-template-columns: minmax(0, 1fr); }
        .pz .pz-detail-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 899px) { .pz .pz-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 639px) {
        .pz .pz-card-head, .pz .pz-card-body { padding: 16px; }
        .pz .pz-row.is-2-1, .pz .pz-row.is-1-1 { grid-template-columns: minmax(0, 1fr); }
        .pz .pz-filters .pz-search, .pz .pz-filters .pz-select { flex: 1 1 100%; max-width: none; }
        .pz .pz-stat-value { font-size: 16px; }
        .pz .pz-stat { padding: 12px; gap: 10px; }
        .pz .pz-stat-icon { width: 32px; height: 32px; flex-basis: 32px; }
        .pz .pz-stat-label { white-space: normal; }
        .pz .pz-choice { grid-template-columns: minmax(0, 1fr); }
    }
</style>
