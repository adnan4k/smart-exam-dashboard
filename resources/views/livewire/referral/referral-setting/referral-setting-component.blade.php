<div>
    <livewire:referral.referral-setting.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <a href="{{ route('referral') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Referrals
                                    </a>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="badge-subtle-brand">Configuration</span>
                                </div>
                                <h5 class="font-bold text-slate-800 text-base mb-0 tracking-tight">Referral Reward Milestones</h5>
                            </div>
                        </div>

                        <button @click="$dispatch('referralSettingModal-')"
                                class="btn-brand text-xs px-4 py-2 shadow-sm flex items-center gap-1.5"
                                type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Reward Rule
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th class="text-center">Required Referrals</th>
                                    <th class="text-center">Reward Bonus</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Date Configured</th>
                                    <th class="text-center w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($referralSettings as $num => $referralSetting)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $num + 1 }}
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-extrabold text-xs font-mono">
                                                <i class="fa-solid fa-user-plus text-[10px] text-slate-400"></i>
                                                <span>{{ $referralSetting->required_referrals }}</span>
                                                <span class="text-[10px] font-medium text-slate-400">Users</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-[#58706D] border border-emerald-100">
                                                <span>{{ number_format($referralSetting->reward_amount, 2) }}</span>
                                                <span class="text-[10px] font-medium text-[#7C8A6E]">ETB</span>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($referralSetting->is_active ?? true)
                                                <span class="badge-subtle-success">Active</span>
                                            @else
                                                <span class="badge-subtle-neutral">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-medium text-slate-600 mb-0">{{ $referralSetting->created_at->format('M d, Y') }}</p>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button @click="$dispatch('edit-referralSetting', { itemId: {{ $referralSetting->id }} })"
                                                        class="action-icon-btn" title="Edit Rule">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>
                                                <button wire:click="$dispatch('openDeleteModal', { itemId: {{ $referralSetting->id }}, model: '{{ addslashes(App\Models\ReferralSetting::class) }}' })"
                                                        class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700" title="Delete Rule">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-12">
                                            <x-empty-state 
                                                title="No referral milestones set" 
                                                subtitle="Define referral reward thresholds to automatically incentivize candidate invitations."
                                                icon="fa-solid fa-sliders" 
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>