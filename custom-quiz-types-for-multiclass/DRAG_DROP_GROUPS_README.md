# Drag-Drop Groups Feature

This feature allows administrators to group answers in matrix sort questions for better organization and visual clarity.

## How to Use

1. **Navigate to a Question Edit Page**
   - Go to WordPress Admin → Questions → Edit a question
   - The question must be of type "Matrix Sort Answer"

2. **Access the Grouping Interface**
   - Look for the "Answer Grouping" section above the answer list
   - This interface will only appear for matrix sort questions

3. **Create Groups**
   - Click on answers to select them (they will be highlighted in blue)
   - Select 2 or more answers to enable the "Group Selected" button
   - Click "Group Selected" to create a group
   - Each group will be assigned a unique color and group number

4. **Manage Groups**
   - View all current groups in the "Current Groups" section
   - Each group shows the number of answers and answer IDs
   - Use the "Ungroup" button to remove a group
   - Use "Clear Selection" to deselect all currently selected answers

5. **Save Groups**
   - Groups are automatically saved when you click the WordPress "Update" button
   - No separate save action is required - groups are saved as part of the normal post save process
   - Groups will persist across page reloads

## Features

- **Visual Grouping**: Each group has a unique color and visual indicator
- **Persistent Storage**: Groups are saved to the database and reload automatically
- **Admin Only**: This feature is only available to administrators
- **Real-time Updates**: The interface updates immediately when groups are created or removed
- **WordPress Integration**: Uses WordPress's native post save mechanism for better reliability
- **Server-side Processing**: Groups are processed server-side during post save for better security

## Technical Details

- **Post Meta Key**: `_mc_drag_drop_groups`
- **Data Structure**: Array of objects with `groupId` and `answerIds` properties
- **Server-side Processing**: Groups are saved during WordPress's `save_post` action
- **Form Integration**: Group data is included in the post form via hidden input field
- **Security**: All data is sanitized and validated server-side before saving

## Architecture

The feature uses a server-side approach where:

1. **JavaScript**: Handles the UI interactions and stores group data in a hidden form field
2. **PHP**: Processes the group data during post save and stores it as post meta
3. **Form Submission**: Group data is automatically included when the post is updated
4. **Data Loading**: Existing groups are loaded server-side and pre-populated in the form

This approach provides better integration with WordPress's native functionality and eliminates the need for custom AJAX endpoints.

## Browser Compatibility

- Modern browsers with ES6+ support
- jQuery 1.12+ required
- WordPress 5.0+ recommended

## Troubleshooting

- **Groups not loading**: Check browser console for AJAX errors
- **Save button not working**: Ensure you have administrator privileges
- **Visual issues**: Check if the question type is "Matrix Sort Answer"
- **Post ID errors**: Verify you're on a valid question edit page 