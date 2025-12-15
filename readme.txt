=== doBoard Add-On for Gravity Forms ===
Contributors: anton_cleantalk, cleantalk, glomberg, alexandergull, sergefcleantalk
Donate link: https://doboard.com/
Tags: gravityforms, doboard, forms, automation, project management
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Organize and track all messages from your site. Gravity Forms, upgraded with Project Management (PM).  

== Description ==

Seamlessly integrate Gravity Forms with doBoard (PM). Automatically create tasks, organize and track every message from site.

This add-on creates tasks in doBoard for every submission made through a [Gravity Forms](https://www.gravityforms.com/) on your site. It automates the processing of leads, orders, support requests, and any other data collected through your websiteвЂ™s forms. Short name (acronym) for the plugin is GF2DB.

[doBoard.com](https://doboard.com/?utm_source=wordpress.org&utm_medium=gf-addon-desc&utm_campaign=doboard-crm) is a project management app (PM). It helps teams communicate with users and manage midterm projects effectively.


= KEY FEATURES =
*	Direct integration between Gravity Forms and doBoard.com. No need for team members to manually create new tasks, which saves time for real work. 
*	Automated task creation from submitted forms.
*	Organization of project contact information.
*	Team collaboration features for sales and support teams
*	A simple alternative to heavyweight CRM platforms
*	Ideal for small and medium teams managing customer relationships, service requests, or sales funnels through WordPress.

= HOW DOES GF2DB WORK? = 

Every form entry goes directly into your doBoard project, creating tasks that can be assigned, tracked, and completed. No manual data transfer, no lost submissionsвЂ”just a clear process from form to action. The workflow like this,

*	Store contact information from submitted forms in organized projects
*	Track customer interactions through comments and task updates
*	Segment contacts by assigning them to specific projects or workflows
*	Track interaction history and follow-up status in a single dashboard

== Installation ==

*	Install the plugin through **the WordPress -> Plugins** screen directly or upload the plugin files to the /wp-content/plugins/cleantalk-doboard-add-on-for-gravity-forms directory.
*	Activate the plugin through the 'Plugins' screen in WordPress.
*	Go to Gravity **Forms -> Settings -> doBoard** and enter your doBoard user token (global setting).
*	Go to **Forms -> Form Settings -> doBoard Feeds** and configure the integration options (account, project, board, labels, etc.).
*	Save your settings. Tasks will now be created automatically on form submission according to your feed configuration.

== Frequently Asked Questions ==

= What do I need to use this plugin? =
You'll need:

>Gravity Forms 2.5 or higher
>An active account at <a href="https://doboard.com/">doBoard</a>

= Where do I get my doBoard user token? =
You can find your token in your doBoard account settings under API section.

= What does GF2DB stand for? =
GF2DB is an acronym for Gravity Forms to doBoard.

== Screenshots ==
1. Plugin settings page.
2. Feed configuration example.
3. Task created in doBoard.

== Changelog ==

= 1.0.5 =
* Upd. Settings. Improve UX.

= 1.0.4 =
* Upd. Resources handling updated.

= 1.0.3 =
* Upd. Implemented changes required by the WordPress review team.
* Upd. Namings.
* Upd. API class initialization refactored.

= 1.0.2 =
* Upd. Implemented changes required by the WordPress review team..
* Fix. Exceptions on task/comment adding when the doBoard license is inactive.

= 1.0.1 =

Docs and readme.txt added.

= 1.0.0 =

Initial release


== Support ==

If you have questions or suggestions, contact us at welcome@cleantalk.org.

== External services ==

This plugin integrates with api.doboard.com service. By using this plugin, you agree to <a href="https://doboard.com/terms-of-use/">terms</a> and <a href="https://doboard.com/privacy-policy/">privacy policy</a> of doBoard service.

Cases of data transmission to this service:

1. User Authentication
Data Sent: Only the user_token as a URL query parameter.

2. Fetching Projects
Data Sent: The account_id (in the path) and session_id (as a URL query parameter).

3. Fetching Task Boards (Tracks)
Data Sent: The account_id (path), session_id, the fixed parameter status=ACTIVE, and an optional project_id (all as URL query parameters).

4. Fetching Labels
Data Sent: The account_id (path) and session_id (as a URL query parameter).

5. Adding a Task
Data Sent: The account_id (in the path). The task data (an array of properties like title, description, etc.) is sent in the body of the POST request, which is encoded as JSON.

6. Adding a Comment
Data Sent: The account_id (in the path). The comment data (e.g., task_id, message, etc.) is sent in the body of the POST request, which is encoded as JSON.
