Agents is a built-in UNA app that brings AI capabilities to your site. It allows you to configure AI assistants, connect AI providers, set up automated workflows, and extend functionality with custom helpers — all from one place.

It includes:
- **[[Assistants|Agents:-Assistants]]** - AI-powered chat agents that can be configured for specific roles and embedded across your site (Studio, Live Search, custom pages)
- **Providers** - [Experimental] needed for Automators
- **Helpers** - [Experimental] needed for Automators
- **Automators** - [Experimental] automated AI workflows triggered by events or schedules, it writes code which can be executed upon some events - Event (UNA alert), Schedule or Webhook

### Settings - General:
- "Default API key" - API key to use with the default model
- "Default Model" - default model to use in different places, such as Assistants, Automators, etc.
- Profile - select user profile which will be used to act as an AI representative

### Settings - Usage (used in Assistants):
- "Auto delete transient chats every day" - automatically deletes transient chats (transient chat is special type of chat with AI Assistant).
- "Assistant for Studio" - assistant to use in Studio; this chat is the same for all Studio Operators.   
  <img width="338" height="307" alt="agents-settings-usage1" src="https://github.com/user-attachments/assets/73c003a8-29f7-4d5e-bc64-e4ca60daf87d" />   
- "Assistant for Live Search" - assistant to use for users in Live Search; upon clicking "Ask:..." a new chat is created. The chat is unique for each user.   
  <img width="576" height="331" alt="agents-settings-usage2" src="https://github.com/user-attachments/assets/027ec725-9c74-4610-9b36-3569e8aab7c6" />   
- "Assistant for 'Ask AI' block" - assistant to use in the "Ask AI" block. This block is not used anywhere by default, so you can add it to any desired page. A new conversation is automatically created as soon as the user sends a new message.   
  <img width="785" height="451" alt="agents-settings-usage3" src="https://github.com/user-attachments/assets/bd4c84a7-ebe1-45e5-8932-e5e83777317d" />