=== Hide This ===
Contributors: andrezrv
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B7XQG5ZA36UZ4
Tags: hide, content, user, role, capability
Requires at least: 3.0
Tested up to: 4.3.1
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides a shortcode that lets you hide some parts of the content from your posts and pages.

== Description ==

**Hide This** provides a `[hide]` shortcode that lets you hide some parts of the content from your posts and pages. You can easily manage inclusions and exclusions for hidden content in three levels: absolute, groups and capabilities, and specific user.

Maybe some part of your post should not be published yet? Or maybe you want to show some specific parts of your content to a certain group or users, let's say your logged in users or your clients? This plugin may be the solution you need.

It's **very important** to note that the content wrapped within the shortcode won't even be printed as HTML. It will be really, really hidden. There are a lot of great plugins that hide the content via CSS and Javascript, but this is not the case. If you want your content to not be visible, but still printed as HTML, you should try one of those.

**Basic usage:**

`[hide]Lorem ipsum dolor sit amet.[/hide]`

This example will hide that content for all the site visitors. But you can be more specific by using attributes.

**Accepted attributes:**

*	**for:** (optional) your rules to hide content. You can use absolute rules (`all`, `none`, `[!]logged`), rules by roles and capabilities (`[!]{role}`, `[!]{role}:[!]{capability}`, `:[!]{capability}`), and rules by specific user (`userid:[!]{ID}`, `useremail:[!]{email}`, `username:[!]{username}`).
*	**exclude:** (optional) your rules to show the hidden content to some specific visitor/s. As in *for*, you can use absolute rules (`all`, `none`, `[!]logged`), rules by roles and capabilities (`[!]{role}`, `[!]{role}:[!]{capability}`, `:[!]{capability}`), and rules by specific user (`userid:[!]{ID}`, `useremail:[!]{email}`, `username:[!]{username}`).
*	**test:** (optional) for debugging purposes. The kind of output you expect. Accepts `content` and `empty`. Use it wisely, because it prints a message with the result of the test.

**Some useful examples:**

Hide your content to all visitors:
`[hide]Lorem ipsum dolor sit amet.[/hide]`

Hide your content to all visitors, except for a specific user:
`[hide for="all" exclude="username:foo"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content to all non-logged visitors:
`[hide for="!logged"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content to all logged in visitors:
`[hide for="logged"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content to all logged in visitors, except for a specific user:
`[hide for="logged" exclude="username:foo"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for some specific role:
`[hide for="contributor"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for two specific roles:
`[hide for="editor, contributor"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for all visitors except for a specific role:
`[hide for="!administrator"]Lorem ipsum dolor sit amet.[/hide]`
`[hide for="all" exclude="administrator"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for all visitors except for two specific roles:
`[hide for="!administrator, !editor"]Lorem ipsum dolor sit amet.[/hide]`
`[hide for="all" exclude="administrator, editor"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for users with a specific role and a specific capability:
`[hide for="some_role:do_a_barrel_roll"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for users with a specific role, not having a specific capability:
`[hide for="some_role:!do_a_barrel_roll"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for users with a specific capability:
`[hide for=":do_a_barrel_roll"]Lorem ipsum dolor sit amet.[/hide]`

Hide your content for a specific user by user name:
`[hide for="username:foo"]`

Hide your content for a specific user by user ID:
`[hide for="userid:42"]`

Hide your content for a specific user by user email:
`[hide for="useremail:foo@mail.com"]`

Hide your content for everyone except for a specific user by user name:
`[hide for="username:!foo"]`

You should get the idea by now. Notice how you can use `!` to deny values such as login status, roles, capabilities and user values.

#### Extending

This plugin offers hooks for filters, so you can modify its functionality or add your own.

* `hide_this_attributes`: Modify the attributes that the shortcode receives.
* `hide_this_content`: Modify the full content that the shortcode prints.
* `hide_this_hide_rules`: Modify rules for hiding content.
* `hide_this_show_rules`: Modify rules for showing content.

#### Contribute

You can make suggestions and submit your own modifications to this plugin on [Github](https://github.com/andrezrv/hide-this).

== Installation ==

1. Unzip `hide-this.zip` and upload the `hide-this` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **"Plugins"** menu in WordPress.
3. Start using the `[hide]` shortcode to hide the content you want.

== Changelog ==

= 1.1.3 =
* Fix: A bug was causing contents always showing for all users when applying negations to roles.

= 1.1.2 =
* Fix: Multiple role assignation wasn't working in some scenarios.

= 1.1.1 =
* Improvement: Parsing shortcodes into `[hide]` and  `[hidethis]`.

= 1.1 =
* Object oriented code.
* New filter hooks.

= 1.0 =
First release!
