document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.admin-profile').forEach((profile) => {
        const visibleRole = profile.querySelector('strong')?.textContent.trim() || 'Staff';
        const user = window.adminUser || { name: visibleRole, email: '', role: visibleRole };
        const adminRoot = window.location.pathname.split('/Admin/')[0] + '/Admin/';
        const adminPath = window.adminPath || adminRoot;
        const logoutPath = window.adminLogout || `${adminPath}logout.php`;
        profile.innerHTML = '';
        profile.classList.add('admin-profile-toggle');
        profile.setAttribute('tabindex', '0');
        profile.setAttribute('role', 'button');
        profile.setAttribute('aria-expanded', 'false');

        const notification = document.createElement('button');
        notification.className = 'admin-notification';
        notification.type = 'button';
        notification.setAttribute('aria-label', 'Open notifications');
        notification.innerHTML = '<i class="fa-regular fa-bell"></i>' + (window.pendingBookings ? `<span>${window.pendingBookings}</span>` : '');

        const avatar = document.createElement('span');
        avatar.className = 'admin-avatar';
        avatar.textContent = user.name.charAt(0).toUpperCase();
        const role = document.createElement('strong');
        role.textContent = user.role;
        const chevron = document.createElement('i');
        chevron.className = 'fa-solid fa-chevron-down';
        profile.append(notification, avatar, role, chevron);

        const menu = document.createElement('div');
        menu.className = 'admin-profile-menu';
        const summary = document.createElement('div');
        summary.className = 'admin-profile-summary';
        const summaryName = document.createElement('strong');
        summaryName.textContent = user.name;
        const summaryEmail = document.createElement('span');
        summaryEmail.textContent = user.email;
        const summaryRole = document.createElement('small');
        summaryRole.textContent = `${user.role} account`;
        summary.append(summaryName, summaryEmail, summaryRole);
        const settingsPath = user.role.toLowerCase() === 'staff' ? './' : adminPath + 'settings/';
        const settingsLabel = user.role.toLowerCase() === 'staff' ? 'Desk overview' : 'Settings';
        menu.append(summary, createMenuLink(settingsPath, 'fa-gear', settingsLabel), createMenuLink(logoutPath, 'fa-arrow-right-from-bracket', 'Log out'));
        profile.appendChild(menu);

        const notificationMenu = document.createElement('div');
        notificationMenu.className = 'admin-notification-menu';
        if (window.pendingBookings) {
            const heading = document.createElement('strong');
            heading.textContent = `${window.pendingBookings} pending booking request${window.pendingBookings === 1 ? '' : 's'}`;
            notificationMenu.append(heading, createMenuLink(window.notificationPath || adminPath + 'bookings/', 'fa-arrow-right', user.role.toLowerCase() === 'staff' ? 'Open queue' : 'Review bookings'));
        } else {
            const heading = document.createElement('strong');
            heading.textContent = 'You are all caught up';
            const empty = document.createElement('span');
            empty.textContent = 'No pending booking requests.';
            notificationMenu.append(heading, empty);
        }
        profile.appendChild(notificationMenu);

        notification.addEventListener('click', (event) => {
            event.stopPropagation();
            notificationMenu.classList.toggle('is-open');
            menu.classList.remove('is-open');
            profile.setAttribute('aria-expanded', 'false');
        });
        profile.addEventListener('click', (event) => {
            if (event.target.closest('.admin-profile-menu, .admin-notification-menu, .admin-notification')) return;
            const open = profile.classList.toggle('is-open');
            profile.setAttribute('aria-expanded', String(open));
        });
        profile.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                profile.classList.toggle('is-open');
                profile.setAttribute('aria-expanded', String(profile.classList.contains('is-open')));
            }
        });
        document.addEventListener('click', (event) => {
            if (!profile.contains(event.target)) {
                profile.classList.remove('is-open');
                notificationMenu.classList.remove('is-open');
                profile.setAttribute('aria-expanded', 'false');
            }
        });
    });
});

function createMenuLink(href, icon, label) {
    const link = document.createElement('a');
    link.href = href;
    const iconElement = document.createElement('i');
    iconElement.className = `fa-solid ${icon}`;
    link.append(iconElement, document.createTextNode(label));
    return link;
}
