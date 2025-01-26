<div>
    <i class="fa fa-user me-sm-1 <?php echo e(in_array(request()->route()->getName(),['profile', 'my-profile']) ? 'text-white' : ''); ?>"></i>
    <span class="d-sm-inline d-none <?php echo e(in_array(request()->route()->getName(),['profile', 'my-profile']) ? 'text-white' : ''); ?>" wire:click="logout">Sign Out</span>
</div>
<?php /**PATH /home/faysal/Desktop/apps/kasma/tour-travel-dashbaord/resources/views/livewire/auth/logout.blade.php ENDPATH**/ ?>