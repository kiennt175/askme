// Editor for question
window.theEditor;
ClassicEditor
    .create(document.querySelector('#editor'), {
        initialData: content,
        licenseKey: 'NA/p3cJE+GCKGiea4vxkQ9/D/W+5t7xlqTtGJx86N6ELM50d2zNNQQPi',
        link: {
            addTargetToExternalLinks: true
        }
    })
    .then(editor => {
        theEditor = editor;
        editor.isReadOnly = true
    })
    .catch(error => console.error(error));
// ------------------------------------------------------------------------------------------------------- //
// Editors for answers


// for (let i = 0; i < editorNumber; i++) {
    
// };
// ------------------------------------------------------------------------------------------------------ //
// Editor for reply
const appDataForReply = {
    // Users data.
    users: [
        {
            id: 'user-' + currentUserId,
            name: currentUserName,
            avatar: currentUserAvatar || 'http://localhost:8000/images/default_avatar.png'
        },
    ],
    // The ID of the current user.
    userId: 'user-' + currentUserId,
};

class CommentsAdapterForReply {
    constructor(editor) {
        this.editor = editor;
    }
    init() {
        const usersPlugin = this.editor.plugins.get('Users');
        const commentsRepositoryPlugin = this.editor.plugins.get('CommentsRepository');
        // Load the users data.
        for (const user of appDataForReply.users) {
            usersPlugin.addUser(user);
        }
        // Set the current user.
        usersPlugin.defineMe(appDataForReply.userId);
    }
}

window.answerEditor; // for postAnswer.js
window.conversation; // for postAnswer.js
ClassicEditor
    .create(document.querySelector('#answer-editor'), {
        initialData: '',
        extraPlugins: [CommentsAdapterForReply],
        licenseKey: 'NA/p3cJE+GCKGiea4vxkQ9/D/W+5t7xlqTtGJx86N6ELM50d2zNNQQPi',
        sidebar: {
            container: document.querySelector('#answer-sidebar')
        },
        toolbar: {
            items: [
                'heading',
                '|',
                'bold',
                'italic',
                'link',
                '|',
                'undo',
                'redo',
                '|',
                'blockquote',
                'comment'
            ]
        },
        link: {
            defaultProtocol: 'https://'
        }
    })
    .then(editor => {
        editor.plugins.get('AnnotationsUIs').switchTo('narrowSidebar');
        // theEditor = editor;
        editor.editing.view.change(writer => {
            writer.setStyle(
                "height",
                "202px",
                editor.editing.view.document.getRoot()
            );
        });
        answerEditor = editor;
        // After the editor is initialized, add an action to be performed after a button is clicked.
        const commentsRepository = editor.plugins.get('CommentsRepository');
        // Get the data on demand.
        if (document.querySelector('#post-answer-button')) {
            document.querySelector('#post-answer-button').addEventListener('click', () => {
                const editorData = editor.data.get();
                const commentThreadsData = commentsRepository.getCommentThreads({
                    skipNotAttached: true,
                    skipEmpty: true,
                    toJSON: true
                });
                // Now, use `editorData` and `commentThreadsData` to save the data in your application.
                // For example, you can set them as values of hidden input fields.
                conversation = JSON.stringify(commentThreadsData);
            });
        }
    })
    .catch(error => console.error(error));
