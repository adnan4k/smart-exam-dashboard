{{--
    Contest palette.

    Roles are assigned by contrast, not by preference:
      ink      #4B5757  white on it = 7.5:1  - emphasis, dark badges
      primary  #58706D  white on it = 5.3:1  - actions, live state
      sage     #7C8A6E  white on it = 3.7:1  - fills and indicators only,
                                               never small text on top
      khaki    #B0B087  borders, rails, muted marks
      cream    #E3E3D1  panel surfaces; ink on it = 5.8:1
--}}
<style>
    .ct {
        --ct-ink:     #4B5757;
        --ct-primary: #58706D;
        --ct-sage:    #7C8A6E;
        --ct-khaki:   #B0B087;
        --ct-cream:   #E3E3D1;
        --ct-surface: #ffffff;
    }

    /* Panels and rails */
    .ct .ct-panel {
        background: var(--ct-cream);
        border: 1px solid var(--ct-khaki);
        border-radius: .5rem;
        color: var(--ct-ink);
    }
    .ct .ct-rail { border-left: 3px solid var(--ct-sage); }
    .ct .ct-divider { border-bottom: 1px solid var(--ct-khaki); }
    .ct .ct-heading { color: var(--ct-ink); }
    .ct .ct-muted { color: var(--ct-primary); opacity: .75; }

    /* Buttons */
    .ct .btn.ct-btn {
        background: var(--ct-primary);
        border-color: var(--ct-primary);
        color: #fff;
    }
    .ct .btn.ct-btn:hover { background: var(--ct-ink); border-color: var(--ct-ink); color: #fff; }
    .ct .btn.ct-btn-quiet {
        background: transparent;
        border: 1px solid var(--ct-primary);
        color: var(--ct-primary);
    }
    .ct .btn.ct-btn-quiet:hover { background: var(--ct-cream); color: var(--ct-ink); }
    .ct .btn.ct-btn-quiet.is-on { background: var(--ct-primary); color: #fff; }

    /* Status badges. Sage and khaki carry no small text - they mark, ink labels. */
    .ct .ct-badge {
        display: inline-block;
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .25rem .55rem;
        border-radius: .375rem;
        line-height: 1.2;
    }
    .ct .ct-badge-draft     { background: var(--ct-cream); color: var(--ct-ink); border: 1px solid var(--ct-khaki); }
    .ct .ct-badge-upcoming  { background: #fff; color: var(--ct-primary); border: 1px solid var(--ct-primary); }
    .ct .ct-badge-live      { background: var(--ct-primary); color: #fff; }
    .ct .ct-badge-awaiting  { background: var(--ct-cream); color: var(--ct-ink); border: 1px solid var(--ct-sage); }
    .ct .ct-badge-finalized { background: var(--ct-ink); color: #fff; }
    .ct .ct-badge-quiet     { background: var(--ct-cream); color: var(--ct-ink); }
    .ct .ct-badge-void      { background: #fff; color: var(--ct-ink); border: 1px dashed var(--ct-khaki); }

    /* A sage dot carries "in progress" without putting text on low contrast. */
    .ct .ct-dot {
        display: inline-block; width: .5rem; height: .5rem;
        border-radius: 50%; background: var(--ct-sage); margin-right: .35rem;
    }

    /* Tables */
    .ct table thead th { color: var(--ct-ink) !important; border-bottom-color: var(--ct-khaki) !important; }
    .ct table tbody tr td { border-bottom-color: rgba(176, 176, 135, .45); }
    .ct .ct-rank { color: var(--ct-ink); font-weight: 700; font-variant-numeric: tabular-nums; }
    .ct .ct-rank-1 { color: var(--ct-primary); }
    .ct .ct-num { font-variant-numeric: tabular-nums; }

    /* Stars read as the reward, so they take the one warm colour in the set. */
    .ct .ct-star { color: var(--ct-sage); font-weight: 700; }

    .ct .form-control:focus {
        border-color: var(--ct-primary);
        box-shadow: 0 0 0 2px rgba(88, 112, 109, .18);
    }
    .ct .form-check-input:checked { background-color: var(--ct-primary); border-color: var(--ct-primary); }

    /* Icon actions. Destructive stays distinguishable from the palette without
       importing a red that fights it - it is the one outlined, dashed mark. */
    .ct .ct-icon-action { color: var(--ct-primary); }
    .ct .ct-icon-action:hover { color: var(--ct-ink); }
    .ct .ct-icon-add { color: var(--ct-sage); }
    .ct .ct-icon-add:hover { color: var(--ct-primary); }
    .ct .ct-icon-danger { color: var(--ct-khaki); }
    .ct .ct-icon-danger:hover { color: var(--ct-ink); }

    .ct .alert-ct-good { background: var(--ct-primary); color: #fff; border: 0; }
    .ct .alert-ct-warn { background: var(--ct-cream); color: var(--ct-ink); border: 1px solid var(--ct-sage); }
    .ct .alert-ct-bad  { background: var(--ct-ink); color: #fff; border: 0; }
</style>
