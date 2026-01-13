<div class="max-w-xl lg:max-w-3xl mx-auto space-y-10 py-8">
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
        <div class="w-80">
            <flux:heading size="lg">Profile</flux:heading>
            <flux:subheading>This is how others will see you on the site.</flux:subheading>
        </div>
        <div class="flex-1 space-y-6">
            <flux:input label="Username"
                description="This is your public display name. It can be your real name or a pseudonym. You can only change this once every 30 days."
                placeholder="calebporzio" />
            <flux:select label="Primary email"
                description:trailing="You can manage verified email addresses in your email settings."
                placeholder="Select primary email...">
                <flux:select.option>lotrrules22@aol.com</flux:select.option>
                <flux:select.option>phantomatrix@hotmail.com</flux:select.option>
            </flux:select>
            <flux:textarea label="Bio"
                description:trailing="You can @mention other users and organizations to link to them."
                placeholder="Tell us a little bit about yourself" />
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Save profile</flux:button>
            </div>
        </div>
    </div>
    <flux:separator variant="subtle" class="my-8" />
</div> 