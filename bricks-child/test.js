const {
    notificationList,
    markAsRead,
    markAllAsReadForUser,
    checkNotificationExists,
    deleteAllNotificaiton,
} = require('../../../models/NotificationModel');

const { sendResponse } = require('../../../../middleware/nyu_auth.headerValidator');

const notification = {

    list: async (req, res) => {
        try 
            const {
                page = 1,
                limit = 10,
            } = req.query;

            const userId = req.loginUser?.user_id;
            let whereData = {};

            whereData.user_id = userId;

            const language_code = (req.headers['accept-language'] || 'en').toString().slice(0, 2);
            const result = await notificationList(whereData, Number(page), Number(limit), language_code);

            return sendResponse(req, res, 200, 'success', { keyword: 'notifications_fetched_success', components: {} }, {
                data: result,
                total: result.total,
                page: result.page,
                pageSize: result.pageSize,
                totalPages: result.totalPages
            });

        } catch (error) {
            console.log('Error fetching notification list:', error);
            return sendResponse(req, res, 500, 'error', { keyword: "something_went_wrong", components: {} }, error?.message);
        }
    },


    markRead: async (req, res) => {
        try {
            const { notification_id } = req?.body || {};
            if (!notification_id) {
                return sendResponse(req, res, 400, 'error', { keyword: 'invalid_notification_id', components: {} });
            }

            const userId = req.loginUser?.user_id;
            // Ensure the notification belongs to the requesting user
            const exists = await checkNotificationExists({ id: BigInt(notification_id), user_id: BigInt(userId) });
            if (!exists) {
                return sendResponse(req, res, 404, 'error', { keyword: 'notification_not_found', components: {} });
            }

            const updated = await markAsRead(notification_id);
            return sendResponse(req, res, 200, 'success', { keyword: 'notification_marked_read_success', components: {} }, updated);
        } catch (error) {
            console.log(error, 'error');
            return sendResponse(req, res, 500, 'error', { keyword: 'something_went_wrong', components: {} }, error?.message);
        }
    

    markAllRead: async (req, res) => {
        try {
            const userId = req.loginUser?.user_id;
            const result = await markAllAsReadForUser(userId);
            return sendResponse(req, res, 200, 'success', { keyword: 'all_notifications_marked_read_success', components: {} }, result);
        } catch (error) {
            console.log(error, 'error');
            return sendResponse(req, res, 500, 'error', { keyword: 'something_went_wrong', components: {} }, error?.message);
        }
    },

    deleteAll: async (req, res) => {
        try {
            const test= ''
            
            const userId = req.loginUser?.user_id;
            const result = await deleteAllNotificaiton(userId);
            return sendResponse(req, res, 200, 'success', { keyword: 'all_notifications_deleted_success', components: {} }, result);
        } catch (error) {
            console.log(error, 'error');
            return sendResponse(req, res, 500, 'error', { keyword: 'something_went_wrong', components: {} }, error?.message);
        }
    
};

module.exports = {
    notification
};