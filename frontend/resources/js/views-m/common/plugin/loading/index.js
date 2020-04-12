import Loadingvue from './index.vue';

const vLoading = {};

vLoading.install = function (Vue, options) {
    const Instance = Vue.extend(Loadingvue);
    Vue.directive('loading', {
        bind: function(el, binding) {
            const Component = new Instance().$mount();
            el.instance = Component;
            el.Component = Component.$el;
            Vue.nextTick(()=>{
                let position = getComputedStyle(el).getPropertyValue("position");
                if(position == 'static') el.style.position = "relative";
                el.instance.show = binding.value.on;
                if(binding.value.bg) el.instance.style.backgroundColor = binding.value.bg;
                if(typeof binding.value.tx !== 'undefined') el.instance.text = binding.value.tx;
                if(typeof binding.value.o === 'boolean') el.instance.circular = binding.value.o;
                if(binding.value.f) el.instance.callback = binding.value.f;
                el.appendChild(el.Component);
            });
        },
        update: function(el, binding) {
            if (binding.oldValue !== binding.value) {
                el.instance.show = binding.value.on;
                if(binding.value.bg) el.instance.style.backgroundColor = binding.value.bg;
                if(typeof binding.value.tx !== 'undefined') el.instance.text = binding.value.tx;
                if(typeof binding.value.o === 'boolean') el.instance.circular = binding.value.o;
                if(binding.value.f) el.instance.callback = binding.value.f;
            }
        }
    });
};
export default vLoading;