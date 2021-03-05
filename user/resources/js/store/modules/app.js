
const state = {
    sidebar: {
        opened: true,//Cookies.get('sidebarStatus') ? !!+Cookies.get('sidebarStatus') : true,
        withoutAnimation: false
    },
    device: 'desktop',
    depositVisible:false,
}

const mutations = {
    TOGGLE_SIDEBAR: state => {
        state.sidebar.opened = !state.sidebar.opened
        state.sidebar.withoutAnimation = false
        if (state.sidebar.opened) {
            // Cookies.set('sidebarStatus', 1)
        } else {
            // Cookies.set('sidebarStatus', 0)
        }
    },
    CLOSE_SIDEBAR: (state, withoutAnimation) => {
        // Cookies.set('sidebarStatus', 0)
        state.sidebar.opened = false
        state.sidebar.withoutAnimation = withoutAnimation
    },
    TOGGLE_DEVICE: (state, device) => {
        state.device = device
    },
    TOGGLE_DEPOSITVISIBLE:(state, status) => {
        state.depositVisible = status
    }
}

const actions = {
    toggleSideBar({ commit }) {
        commit('TOGGLE_SIDEBAR')
    },
    closeSideBar({ commit }, { withoutAnimation }) {
        commit('CLOSE_SIDEBAR', withoutAnimation)
    },
    toggleDevice({ commit }, device) {
        commit('TOGGLE_DEVICE', device)
    },
    toggleDepositVisible({ commit }, status) {
        commit('TOGGLE_DEPOSITVISIBLE', status)
    }
}

export default {
    namespaced: true,
    state,
    mutations,
    actions
}
