document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = Array.from(document.querySelectorAll('.CalcToggleBtn'));
    const sections = Array.from(document.querySelectorAll('.CalcSection'));
    const output = document.getElementById('CalcOutput');

    function setMode(mode) {
        toggleButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
        sections.forEach(sec => {
            sec.style.display = (sec.dataset.mode === mode) ? 'block' : 'none';
        });
        if (output) output.textContent = 'No calculation yet.';
    }

    setMode('carbon');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => setMode(btn.dataset.mode));
    });

    // Elements (IDs normalized to PascalCase)
    const carbonCategory = document.getElementById('CarbonCategory');
    const activityAmount = document.getElementById('ActivityAmount');
    const emissionFactor = document.getElementById('EmissionFactor');
    const calcCarbonBtn = document.getElementById('CalcCarbonBtn');
    const activityLabel = document.getElementById('ActivityLabel');

        const defaultFactors = {
            transport: 2.31, // kg CO2e per litre (gasoline example)
            home: 0.4,       // kg CO2e per kWh (electricity example)
            food: 27         // kg CO2e per kg (beef example)
        };

        function updateCarbonUI() {
            const val = carbonCategory ? carbonCategory.value : '';
            if (!val) {
                activityLabel.textContent = 'Activity amount';
                emissionFactor.value = defaultFactors.home;
            } else if (val === 'transport') {
                activityLabel.textContent = 'Fuel consumed (litres)';
                emissionFactor.value = defaultFactors.transport;
            } else if (val === 'home') {
                activityLabel.textContent = 'Energy used (kWh) — or compute from wattage & hours';
                emissionFactor.value = defaultFactors.home;
            } else if (val === 'food') {
                activityLabel.textContent = 'Food amount (kg)';
                emissionFactor.value = defaultFactors.food;
            }
            if (output) output.textContent = 'No calculation yet.';
        }

        // When the category changes, update the UI labels and default emission factors
        if (carbonCategory) {
            carbonCategory.addEventListener('change', updateCarbonUI);
        }

        // Carbon calculate button — uses CF = A * E (activity × emission factor)
        if (calcCarbonBtn) {
            calcCarbonBtn.addEventListener('click', function () {
                const cat = carbonCategory ? carbonCategory.value : '';
                let activity = parseFloat(activityAmount ? activityAmount.value : '');
                const factor = parseFloat(emissionFactor ? emissionFactor.value : '') || 0;

                if (!cat) {
                    if (output) output.textContent = 'Please select a category.';
                    return;
                }

                if (!Number.isFinite(activity) || activity <= 0) {
                    if (output) output.textContent = 'Please enter a valid activity amount.';
                    return;
                }

                // CF = A * E (kg CO2e). Show in kg and tonnes
                const cfKg = activity * factor;
                const cfTonnes = (cfKg / 1000).toFixed(3);
                if (output) output.textContent = `${cfKg.toFixed(2)} kg CO₂e — ${cfTonnes} tonnes CO₂e`;
            });
        }

        // Energy calculation controls
        // Energy inputs (PascalCase IDs)
        const powerWatts = document.getElementById('PowerWatts');
        const hours = document.getElementById('Hours');
        const hoursUnit = document.getElementById('HoursUnit');
        const costPerKwh = document.getElementById('CostPerKwh');
        const calcEnergyBtn = document.getElementById('CalcEnergyBtn');

        function hoursMultiplier(unit) {
            switch (unit) {
                case 'per_day': return 365;
                case 'per_week': return 52;
                case 'per_month': return 12;
                case 'per_year': return 1;
                default: return 1;
            }
        }

        if (calcEnergyBtn) {
            calcEnergyBtn.addEventListener('click', function () {
                const w = parseFloat(powerWatts ? powerWatts.value : '');
                const h = parseFloat(hours ? hours.value : '');
                const unit = hoursUnit ? hoursUnit.value : 'per_day';
                const multiplier = hoursMultiplier(unit);

                if (!Number.isFinite(w) || w <= 0) {
                    if (output) output.textContent = 'Enter a valid power in watts.';
                    return;
                }
                if (!Number.isFinite(h) || h < 0) {
                    if (output) output.textContent = 'Enter a valid hours value.';
                    return;
                }

                // kWh per year = (Watts * hours * multiplier) / 1000
                const annualKwh = (w * h * multiplier) / 1000;
                let text = `${annualKwh.toFixed(2)} kWh per year`;

                // If cost provided, compute annual cost
                const cost = parseFloat(costPerKwh ? costPerKwh.value : '');
                if (Number.isFinite(cost) && cost >= 0) {
                    const annualCost = annualKwh * cost;
                    text += ` — Estimated annual cost: £${annualCost.toFixed(2)}`;
                }

                // Also estimate CO2 using default electricity factor (0.4 kg/kWh)
                const cfKg = annualKwh * 0.4; // kg CO2e
                const cfTonnes = (cfKg / 1000).toFixed(3);
                text += ` — ≈ ${cfTonnes} tonnes CO₂e per year`;

                if (output) output.textContent = text;
            });
        }

        // Modal behavior for Find out more
        // Modal elements (PascalCase IDs)
        const findBtn = document.getElementById('FindOutMoreBtn');
        const modal = document.getElementById('ModalOverlay');
        const modalClose = document.getElementById('ModalClose');

        function openModal() {
            if (!modal) return;
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            // focus the close button for accessibility
            if (modalClose) modalClose.focus();
        }

        function closeModal() {
            if (!modal) return;
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (findBtn) findBtn.focus();
        }

        if (findBtn) {
            findBtn.addEventListener('click', openModal);
        }
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        if (modal) {
            modal.addEventListener('click', function (e) {
                // close when clicking overlay (but not when clicking modal content)
                if (e.target === modal) closeModal();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

});
