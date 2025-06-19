<div class="main-content">
    <livewire:referral.form />
    <livewire:referral.referral-setting.form />

    <!-- Referral Settings Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Referral Settings</h5>
                        </div>
                        <!-- <button
                            style="background-color:#56C596;"
                            @click="$dispatch('referralSettingModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Setting</button> -->
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Required Referrals
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Reward Amount
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Status
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referralSettings as $setting)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $setting->required_referrals }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($setting->reward_amount, 2) }} ETB</p>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-sm {{ $setting->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="$dispatch('edit-referralSetting', { referralSetting: {{ $setting->id }} })"
                                                class="text-blue-500">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                wire:click="$dispatch('openDeleteModal', { itemId: {{ $setting->id }}, model: '{{ addslashes(App\Models\ReferralSetting::class) }}' })"
                                                class="text-red-500">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Referrals</h5>
                        </div>
                        <!-- <button
                            style="background-color:#56C596;"
                            @click="$dispatch('referralModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Referral</button> -->
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        ID
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Referrer Name
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Referred Name
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Bonus
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Payment Status
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Created At
                                    </th>
                                    <!-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referrals as $num => $referral)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $num + 1 }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $referral->referrer->name ?? 'N/A' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $referral->referred->name ?? 'N/A' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($referral->bonus_amount, 2) }} ETB</p>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $referral->is_paid ? 'bg-success' : 'bg-warning' }}">
                                                {{ $referral->is_paid ? 'Paid' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $referral->created_at->format('Y-m-d') }}</p>
                                        </td>
                                        <td class="text-center">
                                            <!-- <button
                                                @click="$dispatch('edit-referral', { referral: {{ $referral->id }} })"
                                                class="text-blue-500">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                wire:click="$dispatch('openDeleteModal', { itemId: {{ $referral->id }}, model: '{{ addslashes(App\Models\Referral::class) }}' })"
                                                class="text-red-500">
                                                <i class="fa-solid fa-trash"></i>
                                            </button> -->
                                            <button
                                                wire:click="togglePaymentStatus({{ $referral->id }})"
                                                class="btn btn-sm {{ $referral->is_paid ? 'btn-warning' : 'btn-success' }}">
                                                {{ $referral->is_paid ? 'Mark as Pending' : 'Mark as Paid' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
